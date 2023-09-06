<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BuyThankYouController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    #[Route('/buy/{slug}/thank-you/{reference}', name: 'buy_thank_you')]
    public function thankYou(string $slug, string $reference) : Response
    {
        $ticket = $this->ticketRepository->findOneBy(['reference' => $reference]);
        return $this->render('buy_thank_you.html.twig', [
            'ticket' => $ticket
        ]);
    }
}