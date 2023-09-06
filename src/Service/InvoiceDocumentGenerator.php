<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Ticket;
use Knp\Snappy\Pdf;
use Twig\Environment;

class InvoiceDocumentGenerator
{
    public function __construct(
        private Environment $twig,
        private Pdf $pdf
    ) {}
    public function html(Invoice $invoice):string
    {

        $details = [];
        $global = ['subtotal' => 0.0, 'total' => 0.0, 'tax' => 0.0];

        /** @var Ticket $ticket */
        foreach ($invoice->getTickets() as $ticket) {
            $details[] = [
                'concept' => 'Entrada PulpoCon23 ' . $ticket->getReference(),
                'price' => $ticket->getPrice(),
                'withTax' => $ticket->getPrice() + $ticket->getTax()
            ];

            $global['subtotal'] += $ticket->getPrice();
            $global['tax'] += $ticket->getTax();
            $global['total'] += $ticket->getPrice() + $ticket->getTax();
        }

        return $this->twig->render('pdf/invoice.html.twig', [
            'invoice' => $invoice,
            'details' => $details,
            'global' => $global,
            'blankLines' => 15 - count($details)
        ]);
    }

    public function pdf(Invoice $invoice): string
    {
        return $this->pdf->getOutputFromHtml(
            $invoice->getDocument(),
            [
                'page-size' => 'A4',
                'margin-top' => '0in',
                'margin-right' => '0in',
                'margin-bottom' => '0in',
                'margin-left' => '0in',
                'encoding' => "UTF-8",
                'no-outline' => null
            ]
        );
    }
}
