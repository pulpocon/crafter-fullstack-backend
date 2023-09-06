<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Ticket;
use Endroid\QrCode\Builder\BuilderRegistryInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SendTicketEmail
{
    public function __construct(
        private MailerInterface           $mailer,
        private BuilderRegistryInterface  $builderRegistry,
        private Environment               $twig,
        private Pdf                       $pdf
    ) {}

    /**
     * @param Ticket $ticket
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(Ticket $ticket) : void
    {
        $ticketPlan = $ticket->getTicketPlan();
        if (null === $ticketPlan) {
            return;
        }

        $builder = $this->builderRegistry->getBuilder('default');
        $builder->data($ticket->getReference())->build();

        $accessTo = $ticketPlan->getAccessTo();
        $pdf = [];
        foreach ($accessTo as $access) {
            $pdf[] = [
                'name' => 'pulpoCon23-' . $access . '-' . $ticket->getReference() . '.pdf',
                'document' => $this->pdf->getOutputFromHtml($this->twig->render('pdf/portrait/pdf.html.twig', [
                    'ticket' => $ticket,
                    'access' => $access
                ]))
            ];
        }

        $email = (new Email())
            ->from('hello@pulpocon.es')
            ->to($ticket->getEmail())
            ->subject('🐙 #pulpoCon23 - Gracias por adquirir tu entrada ' . $ticketPlan->getName())
            ->text('thanks')
            ->embed($builder->data($ticket->getReference())->build()->getString(), 'QR_' . $ticket->getReference() . '.png')
            ->html($this->twig->render('mail/thank_you.html.twig', [
                'ticket' => $ticket
            ]));

        foreach ($pdf AS $document) {
            $email->attach($document['document'], $document['name']);
        }

        $this->mailer->send($email);
    }
}
