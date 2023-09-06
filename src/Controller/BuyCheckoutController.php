<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\TicketAbandoned;
use App\Repository\TicketAbandonedRepository;
use App\Repository\TicketRepository;
use App\Service\SendTicketEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BuyCheckoutController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private TicketAbandonedRepository $ticketAbandonedRepository,
        private SendTicketEmail $sendTicketEmail
    ) {}

    #[Route('/buy/{slug}/checkout/{reference}', name: 'buy_checkout')]
    public function checkout(string $slug, string $reference) : Response
    {
        $ticket = $this->ticketRepository->findOneBy(['reference' => $reference]);
        if (null === $ticket) {
            throw new NotFoundHttpException();
        }

        if (null !== $ticket->getEndDate()) {
            return $this->renderForm('buy_already_paid.html.twig', [
                'ticket' => $ticket
            ]);
        }

        $ticketOrigin = $ticket->getUpgradedFrom();
        $timeFromStartDateToNow = $ticket->getStartDate()->diff(new \DateTimeImmutable());

        if ($timeFromStartDateToNow->i >= 5) {
            $ticketAbandoned = TicketAbandoned::fromTicket($ticket);
            $this->ticketAbandonedRepository->add($ticketAbandoned, true);
            $this->ticketRepository->remove($ticket, true);

            return $this->renderForm('buy_timeout.html.twig', [
                'ticket' => $ticketAbandoned
            ]);
        }

        if (true === $ticket->getTicketPlan()->isFree()) {
            $ticket->finish();
            $this->ticketRepository->add($ticket, true);
            try {
                $this->sendTicketEmail->__invoke($ticket);
            } catch (\Throwable $e) {

            }
            return $this->redirectToRoute('buy_thank_you', ['slug' => $slug, 'reference' => $ticket->getReference()]);
        }

        return $this->renderForm('buy_checkout.html.twig', [
            'ticket' => $ticket,
            'ticketOrigin' => $ticketOrigin
        ]);
    }
}
