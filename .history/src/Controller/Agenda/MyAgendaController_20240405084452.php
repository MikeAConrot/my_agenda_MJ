<?php

namespace App\Controller\Agenda;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class MyAgendaController extends AbstractController
{
   
    
    public function index()
    {
        
        $manager =  $manager = $this->doctrine->getManager();
        return $this->render('my_agendamj\index.html.twig');
    }
}
