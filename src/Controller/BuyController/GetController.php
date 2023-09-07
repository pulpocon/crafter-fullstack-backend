<?php

namespace App\Controller\BuyController;

use App\Entity\Ticket;
use App\Entity\TicketPlan;
use App\Form\TicketType;
use App\Form\TicketUpgradeType;
use App\Repository\TicketPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GetController extends AbstractController
{
    public function __construct(
        private TicketPlanRepository $ticketPlanRepository
    ) {}

    #[Route('/buy/{slug}/attendee', name: 'buy_attendee', methods: ['get'])]
    public function index(string $slug): Response
    {
        $ticketPlan = $this->obtainTicketPlan($slug);
        if (false === $ticketPlan->canBeBought()) {
            return $this->getNotAvailableViewToRender($ticketPlan);
        }

        $ticket = Ticket::fromPlan($ticketPlan);
        return $this->getBuyAttendeeViewToRender($ticket);
    }

    private function obtainTicketPlan(string $slug): ?TicketPlan
    {
        $ticketPlan = $this->ticketPlanRepository->findOneBy(['slug' => $slug]);
        $this->ticketPlanIsValidOrFail($ticketPlan);
        return $ticketPlan;
    }

    private function ticketPlanIsValidOrFail(?TicketPlan $ticketPlan): void
    {
        if (null === $ticketPlan || !$ticketPlan->isActive()) {
            throw new NotFoundHttpException();
        }
    }


    private function getNotAvailableViewToRender(?TicketPlan $ticketPlan): Response
    {
        return $this->renderForm('buy_not_available.html.twig', [
            'ticketPlan' => $ticketPlan
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $formUpgrade
     * @param \Symfony\Component\Form\FormInterface $form
     * @param Ticket $ticket
     * @return Response
     */
    private function getBuyAttendeeViewToRender(Ticket $ticket): Response
    {
        $formUpgrade = $this->createForm(TicketUpgradeType::class, null);
        $form = $this->createForm(TicketType::class, $ticket);

        return $this->renderForm('buy_attendee.html.twig', [
            'formUpgrade' => $formUpgrade,
            'form' => $form,
            'ticket' => $ticket
        ]);
    }

}
