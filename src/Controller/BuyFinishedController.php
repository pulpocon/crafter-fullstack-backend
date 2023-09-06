<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\PaypalDetails;
use App\Entity\PurchaseError;
use App\Entity\Ticket;
use App\Entity\TicketPlan;
use App\Repository\PaypalDetailsRepository;
use App\Repository\PurchaseErrorRepository;
use App\Repository\TicketRepository;
use App\Service\PayPalConnection;
use App\Service\SendTicketEmail;
use JsonException;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BuyFinishedController extends AbstractController
{
    public function __construct(
        private TicketRepository          $ticketRepository,
        private PaypalDetailsRepository   $paypalDetailsRepository,
        private PurchaseErrorRepository   $purchaseErrorRepository,
        private SendTicketEmail $sendTicketEmail,
        private PayPalConnection $payPalConnection
    ) { }

    /**
     * @throws JsonException
     */
    #[Route('/buy/{slug}/finish/{reference}', name: 'buy_finish')]
    public function finished(string $reference, Request $request): Response
    {
        $ticket = $this->ticketRepository->findOneBy(['reference' => $reference]);
        if (null === $ticket) {
            $this->purchaseErrorRepository->add(
                (new PurchaseError())
                    ->setError('ticket not found')->setReference($reference),
                true
            );
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $paypalReturnData = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $result = $this->payPalConnection->client()->execute(new OrdersGetRequest($paypalReturnData->id));

        if ($result->statusCode !== 200) {
            $this->purchaseErrorRepository->add(
                (new PurchaseError())
                    ->setError('paypal status not valid')
                    ->setReference($reference)
                    ->setPaypalId($paypalReturnData->result->id)
                    ->setDetails($paypalReturnData->result)
                    ->setStatus('' . $result->statusCode),
                true
            );
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = $result->result;
        if ($this->purchaseIsInvalid($ticket, $data)) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $paypalDetails = new PaypalDetails();
        $paypalDetails->setReference($ticket->getReference());
        $paypalDetails->setPaypalId($data->id);
        $paypalDetails->setStatus($data->status);
        $paypalDetails->setPaid($data->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->gross_amount->value);

        if (property_exists($data->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown, 'paypal_fee')) {
            $paypalDetails->setFee($data->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->value);
        }

        if (property_exists($data->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown, 'net_amount')) {
            $paypalDetails->setNetAmount($data->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->net_amount->value);
        }
        $paypalDetails->setDetails($data);
        $ticket->finish();

        $this->paypalDetailsRepository->add($paypalDetails, true);
        $this->ticketRepository->add($ticket, true);

        try {
            $this->sendTicketEmail->__invoke($ticket);
        } catch (\Throwable $e) {

        }

        return new Response();
    }

    private function purchaseIsInvalid(Ticket $ticket, object $data) : bool
    {
        return $this->ticketIsNotValid($ticket, $data) ||
            $this->orderIsNotCompleted($ticket, $data) ||
            $this->orderNotHasOnlyOnePurchaseUnit($ticket, $data) ||
            $this->orderIsNotPaidInEuros($ticket, $data) ||
            $this->orderPaidIsNotTheExpectedQuantity($ticket, $data);
    }

    private function ticketIsNotValid(Ticket $ticket, $data) : bool
    {
        $purchase = $data->purchase_units[0];
        $result = null === $ticket->getTicketPlan() || false === $ticket->hashIsValid($purchase->custom_id);
        if (true === $result) {
            $ticketError = (new PurchaseError())
                ->setError('ticketIsNotValid')
                ->setReference($ticket->getReference())
                ->setPaypalId($data->id)
                ->setDetails($data);
            $this->purchaseErrorRepository->add($ticketError, true);
        }

        return $result;
    }

    private function orderIsNotCompleted(Ticket $ticket, $data) : bool
    {
        $result = $data->status !== "COMPLETED";
        if (true === $result) {
            $ticketError = (new PurchaseError())
                ->setError('orderIsNotCompleted')
                ->setReference($ticket->getReference())
                ->setPaypalId($data->id)
                ->setDetails($data)
                ->setStatus($data->status);
            $this->purchaseErrorRepository->add($ticketError, true);
        }

        return $result;
    }

    private function orderNotHasOnlyOnePurchaseUnit(Ticket $ticket, $data) : bool
    {
        $purchaseUnits = $data->purchase_units;
        $result = !is_array($purchaseUnits) || count($purchaseUnits) !== 1;
        if (true === $result) {
            $ticketError = (new PurchaseError())
                ->setError('orderNotHasOnlyOnePurchaseUnit')
                ->setReference($ticket->getReference())
                ->setPaypalId($data->id)
                ->setDetails($data)
                ->setStatus($data->status);
            $this->purchaseErrorRepository->add($ticketError, true);
        }

        return $result;
    }

    private function orderIsNotPaidInEuros(Ticket $ticket, object $data) : bool
    {
        $purchaseUnit = $data->purchase_units[0];
        if (!is_array($purchaseUnit->payments->captures) && count ($purchaseUnit->payments->captures) !== 1) {
            $ticketError = (new PurchaseError())
                ->setError('orderIsNotPaidInEuros')
                ->setReference($ticket->getReference())
                ->setPaypalId($data->id)
                ->setDetails($data)
                ->setStatus($data->status);
            $this->purchaseErrorRepository->add($ticketError, true);
            return true;
        }

        $result = $purchaseUnit->payments->captures[0]->amount->currency_code !== 'EUR';
        if (true === $result) {
            $ticketError = (new PurchaseError())
                ->setError('orderIsNotPaidInEuros')
                ->setReference($ticket->getReference())
                ->setPaypalId($data->id)
                ->setDetails($data)
                ->setStatus($data->status);
            $this->purchaseErrorRepository->add($ticketError, true);
        }
        return $result;
    }

    private function orderPaidIsNotTheExpectedQuantity(Ticket $ticket, object $data) : bool
    {
        /** @var TicketPlan $ticketPlan */
        $ticketPlan = $ticket->getTicketPlan();
        $purchaseUnit = $data->purchase_units[0];

        $result = $ticket->isFullyPaid($purchaseUnit->payments->captures[0]->amount->value);
        if (true === $result) {
            $ticketError = (new PurchaseError())
                ->setError('orderPaidIsNotTheExpectedQuantity')
                ->setReference($ticket->getReference())
                ->setPaypalId($data->id)
                ->setDetails($data)
                ->setStatus($data->status)
                ->setPaid($purchaseUnit->payments->captures[0]->amount->value);
            $this->purchaseErrorRepository->add($ticketError, true);
        }
        return $result;
    }
}
