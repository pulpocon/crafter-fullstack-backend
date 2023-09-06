<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Form\TicketUpgradeType;
use App\Repository\TicketPlanRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BuyController extends AbstractController
{
    public function __construct(
        private TicketPlanRepository $ticketPlanRepository,
        private TicketRepository $ticketRepository
    ) {}

    #[Route('/buy/{slug}/attendee', name: 'buy_attendee')]
    public function index(string $slug, Request $request) : Response
    {
        $ticketPlan = $this->ticketPlanRepository->findOneBy(['slug' => $slug]);
        if (null === $ticketPlan || !$ticketPlan->isActive()) {
            throw new NotFoundHttpException();
        }

        if ($ticketPlan->getAvailableTickets() === 0) {
            return $this->renderForm('buy_not_available.html.twig', [
                'ticketPlan' => $ticketPlan
            ]);
        }

        $formUpgrade = $this->createForm(TicketUpgradeType::class, null);
        $formUpgrade->handleRequest($request);

        if ($formUpgrade->isSubmitted() && $formUpgrade->isValid()) {
            $data = $formUpgrade->getData();
            $ticket = Ticket::fromTicket($this->ticketRepository->findOneBy(['reference' => $data['reference']]));
            $ticket->setTicketPlan($ticketPlan);
        } else {
            $ticket = Ticket::fromPlan($ticketPlan);
            if ($request->request->has('ticket')) {
                $formData = $request->request->all()['ticket'];
                if (array_key_exists('upgradedFrom', $formData) && $formData['upgradedFrom']) {
                    $ticket->setUpgradedFrom($this->ticketRepository->find($formData['upgradedFrom']));
                }
            }
        }
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->reconcile();
            $this->ticketRepository->add($ticket, true);
            return $this->redirectToRoute('buy_checkout', ['slug' => $slug, 'reference' => $ticket->getReference()]);
        }

        return $this->renderForm('buy_attendee.html.twig', [
            'formUpgrade' => $formUpgrade,
            'form' => $form,
            'ticket' => $ticket
        ]);
    }

}
