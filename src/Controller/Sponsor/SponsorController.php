<?php

declare(strict_types=1);

namespace App\Controller\Sponsor;

use App\Entity\AttendeeInfo;
use App\Entity\Lead;
use App\Repository\AttendeeInfoRepository;
use App\Repository\LeadRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SponsorController extends AbstractController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private AttendeeInfoRepository $attendeeInfoRepository,
        private LeadRepository $leadRepository,
        private TicketRepository $ticketRepository
    ) { }

    #[Route('/sponsor', name: 'sponsor_leads')]
    public function leads(): Response
    {
        return $this->renderForm('sponsor/leads.html.twig', [
            'leads' => $this->leadRepository->findBy(['sponsor' => $this->getUser()->getUserIdentifier()])
        ]);
    }

    #[Route('/sponsor/scan', name: 'sponsor_scan')]
    public function scan(): Response
    {
        return $this->renderForm('sponsor/scan.html.twig', []);
    }

    #[Route('/sponsor/scan/{reference}', name: 'sponsor_add_reference')]
    public function addReference(string $reference): Response
    {
        $lead = new Lead($this->getUser()->getUserIdentifier(), $this->obtainAttendeeInfo($reference));
        $this->leadRepository->add($lead, true);

        return new RedirectResponse($this->urlGenerator->generate(
            'sponsor_lead_information',
            ['id' => $lead->getId()]
        ));
    }

    private function obtainAttendeeInfo(string $reference): AttendeeInfo
    {
        $attendeeInfo = $this->attendeeInfoRepository->findOneBy(['reference' => $reference]);
        if ($attendeeInfo === null) {
            $attendeeInfo = AttendeeInfo::fromTicket(
                $this->ticketRepository->findOneBy(['reference' => $reference])
            );
            $this->attendeeInfoRepository->add($attendeeInfo);
        }

        return $attendeeInfo;
    }

    #[Route('/sponsor/lead/{id}', name: 'sponsor_lead_information')]
    public function info(int $id): Response
    {
        $lead = $this->leadRepository->find($id);
        if ($lead === null || $lead->getAttendeeInfo() === null || $lead->getSponsor() !== $this->getUser()->getUserIdentifier()) {
            return new RedirectResponse($this->urlGenerator->generate('sponsor_leads'));
        }

        return $this->renderForm('sponsor/info.html.twig', [
            'attendee' => $lead->getAttendeeInfo()
        ]);
    }
}