<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyAgendaController extends AbstractController
{
   
    public function index(): Response
    {
        return $this->render('my_agenda/index.html.twig', [
            'controller_name' => 'MyAgendaController',
        ]);
    }
}
