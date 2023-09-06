<?php

declare(strict_types=1);

namespace App\Controller\Acreditation;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccreditationController extends AbstractController
{
    public function __construct(private TicketRepository $ticketRepository)
    { }

    #[Route('/accreditation', name: 'accreditation_scan')]
    public function scan() : Response
    {
        return $this->renderForm('accreditation/scan.html.twig', []);
    }

    #[Route('/accreditation/{reference}', name: 'accreditation_information')]
    public function info(string $reference) : Response
    {
        $attendee = $this->ticketRepository->findOneBy(['reference' => $reference]);

        if ($attendee->canAccess()) {
            $attendee->registered();
        }

        $template = $attendee->canAccess() ? 'accreditation/info.html.twig' : 'accreditation/error.html.twig';
        return $this->renderForm($template, [
            'attendee' => $attendee
        ]);
    }
}
