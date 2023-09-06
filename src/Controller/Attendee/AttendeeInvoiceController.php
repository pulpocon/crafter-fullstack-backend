<?php

declare(strict_types=1);

namespace App\Controller\Attendee;

use App\Entity\Invoice;
use App\Entity\Ticket;
use App\Form\AttendeeInvoiceAccessType;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use App\Repository\TicketRepository;
use App\Service\InvoiceDocumentGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class AttendeeInvoiceController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private InvoiceRepository $invoiceRepository,
        private InvoiceDocumentGenerator $invoiceDocumentGenerator
    ) {}

    #[Route('/attendee/invoice', name: 'attendee_invoice')]
    public function info(Request $request) : Response
    {
        if ($request->request->has('invoice')) {
            $formData = $request->request->all()['invoice'];
            return $this->attendeeInvoice(
                $request,
                $this->obtainInvoice($formData['accessReference'], $formData['accessEmail'])
            );
        }

        $formAccess = $this->createForm(AttendeeInvoiceAccessType::class, null);
        $formAccess->handleRequest($request);
        if ($formAccess->isSubmitted() && $formAccess->isValid()) {
            $data = $formAccess->getData();
            $invoice = $this->obtainInvoice($data['reference'], $data['email']);

            return $this->attendeeInvoice($request, $invoice);
        }

        return $this->renderForm('attendee/access.html.twig', [
            'title' => 'Attendee Invoice',
            'formAccess' => $formAccess
        ]);
    }

    private function obtainInvoice(string $reference, string $email): Invoice
    {
        $ticket = $this->ticketRepository->findOneBy(['reference' => $reference]);
        $invoice = $ticket->getInvoice();
        if ($invoice === null) {
            $invoice = Invoice::fromTicket($ticket);
        }
        $invoice->addAccessReference($reference);
        $invoice->addAccessEmail($email);

        return $invoice;
    }

    private function attendeeInvoice(Request $request, Invoice $invoice) : Response
    {
        if ($invoice->getId() !== null) {
            return $this->downloadInvoice($invoice);
        }

        $formInfo = $this->createForm(InvoiceType::class, $invoice);
        $formInfo->handleRequest($request);
        if ($formInfo->isSubmitted() && $formInfo->isValid()) {
            $tickets = $invoice->getTickets();
            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $ticket->setInvoice($invoice);
                $this->ticketRepository->add($ticket);
            }
            $this->invoiceRepository->add($invoice, true);
            $invoice->addDocument($this->invoiceDocumentGenerator->html($invoice));
            $this->invoiceRepository->add($invoice, true);
            return $this->downloadInvoice($invoice);
        }

        return $this->renderForm('attendee/invoice.html.twig', [
            'title' => 'Attendee Invoice',
            'formInvoice' => $formInfo
        ]);
    }

    private function downloadInvoice(Invoice $invoice): Response
    {
        $response = new Response($this->invoiceDocumentGenerator->pdf($invoice));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'invoice-' . $invoice->getId() . '.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
