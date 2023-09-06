<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/condiciones-entrada', name: 'condiciones-entrada')]
    public function tickets() : Response
    {
        return $this->render('condiciones_entrada.html.twig');
    }

    #[Route('/politica-privacidad', name: 'politica-privacidad')]
    public function privacy() : Response
    {
        return $this->render('politica_privacidad.html.twig');
    }
}