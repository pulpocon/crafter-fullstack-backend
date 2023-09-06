<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TicketPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private TicketPlanRepository $ticketPlanRepository) {}

    #[Route('/', name: 'home')]
    public function index() : Response
    {
        return $this->render('home.html.twig', [
            'ticketPlanArray' => $this->ticketPlanRepository->findBy(
                ['visible' => true],
                ['position' => 'ASC']
            )
        ]);
    }
}
