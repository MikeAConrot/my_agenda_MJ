<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PhoneController extends AbstractController
{
    #[Route('/agenda/phone', name: 'app_agenda_phone')]
    public function index(): Response
    {
        return $this->render('agenda/phone/index.html.twig', [
            'controller_name' => 'PhoneController',
        ]);
    }
}
