<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyAgendaController extends AbstractController
{
   
    public function index()
    {
        return $this->render('my_agendamj\index.html.twig');

    }


   
}
