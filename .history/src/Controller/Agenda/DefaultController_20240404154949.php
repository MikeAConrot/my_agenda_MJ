<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/agenda/default', name: 'app_agenda_default')]
    public function index(): Response
    {
        return $this->render('agenda/default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
