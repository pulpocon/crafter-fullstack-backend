<?php

declare(strict_types=1);

namespace App\Controller\BuyController;

use App\Entity\Ticket;
use App\Entity\TicketPlan;
use App\Form\TicketType;
use App\Form\TicketUpgradeType;
use App\Repository\TicketPlanRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    public function __construct(
        private TicketPlanRepository $ticketPlanRepository,
        private TicketRepository $ticketRepository
    ) {}

    #[Route('/buy/{slug}/attendee', name: 'buy_attendee_post', methods: ['post'])]
    public function handlePost(string $slug, Request $request) : Response
    {
        $ticketPlan = $this->obtainTicketPlan($slug);
        if (false === $ticketPlan->canBeBought()) {
            return $this->getNotAvailableViewToRender($ticketPlan);
        }

        list($formUpgrade, $ticket) = $this->upgradeTicket($request, $ticketPlan);

        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->reconcile();
            $this->ticketRepository->add($ticket, true);
            return $this->redirectToRoute('buy_checkout', ['slug' => $slug, 'reference' => $ticket->getReference()]);
        }

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


    private function upgradeTicket(Request $request, TicketPlan $ticketPlan): array
    {
        $formUpgrade = $this->createForm(TicketUpgradeType::class, null);
        $formUpgrade->handleRequest($request);

        if (!$formUpgrade->isSubmitted() || !$formUpgrade->isValid()) {
            $ticket = Ticket::fromPlan($ticketPlan);
            if ($request->request->has('ticket')) {
                $formData = $request->request->all()['ticket'];
                if (array_key_exists('upgradedFrom', $formData) && $formData['upgradedFrom']) {
                    $ticket->setUpgradedFrom($this->ticketRepository->find($formData['upgradedFrom']));
                }
            }
            return array($formUpgrade, $ticket);
        }

        $data = $formUpgrade->getData();
        $ticket = Ticket::fromTicket($this->ticketRepository->findOneBy(['reference' => $data['reference']]));
        $ticket->setTicketPlan($ticketPlan);
        return array($formUpgrade, $ticket);
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
