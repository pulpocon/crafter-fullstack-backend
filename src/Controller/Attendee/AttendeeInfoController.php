<?php

namespace App\Controller\Attendee;

use App\Entity\AttendeeInfo;
use App\Entity\Ticket;
use App\Form\AttendeeInfoAccessType;
use App\Form\AttendeeInfoType;
use App\Repository\AttendeeInfoRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class AttendeeInfoController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private AttendeeInfoRepository $attendeeInfoRepository
    ) {}

    #[Route('/attendee', name: 'attendee_info')]
    public function info(Request $request) : Response
    {
        if ($request->request->has('attendee_info')) {
            $formData = $request->request->all()['attendee_info'];
            return $this->attendeeInfo(
                $request,
                $this->obtainAttendeeInfo($formData['reference'], $formData['email'])
            );
        }

        $formAccess = $this->createForm(AttendeeInfoAccessType::class, null);
        $formAccess->handleRequest($request);

        if ($formAccess->isSubmitted() && $formAccess->isValid()) {

            $data = $formAccess->getData();
            $attendeeInfo = $this->obtainAttendeeInfo($data['reference'], $data['email']);

            return $this->attendeeInfo($request, $attendeeInfo);
        }

        return $this->renderForm('attendee/access.html.twig', [
            'title' => 'Attendee Profile',
            'formAccess' => $formAccess
        ]);
    }

    private function attendeeInfo(Request $request, AttendeeInfo $attendeeInfo) : Response
    {
        $formInfo = $this->createForm(AttendeeInfoType::class, $attendeeInfo);
        $formInfo->handleRequest($request);
        if ($formInfo->isSubmitted() && $formInfo->isValid()) {
            $this->attendeeInfoRepository->add($attendeeInfo, true);
        }

        return $this->renderForm('attendee/info.html.twig', [
            'title' => 'Attendee Profile',
            'formInfo' => $formInfo
        ]);
    }

    private function obtainAttendeeInfo(string $reference, string $email): AttendeeInfo
    {
        $attendeeInfo = $this->attendeeInfoRepository->findOneBy(['reference' => $reference]);
        if ($attendeeInfo === null) {
            $attendeeInfo = AttendeeInfo::fromTicket(
                $this->ticketRepository->findOneBy(['reference' => $reference])
            );
            $this->attendeeInfoRepository->add($attendeeInfo);
        }

        $attendeeInfo->setTicketEmail($email);
        $attendeeInfo->setTicketReference($reference);

        return $attendeeInfo;
    }

}
