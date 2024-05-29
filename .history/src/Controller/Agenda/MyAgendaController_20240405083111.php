<?php

namespace App\Controller\Agenda;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class MyAgendaController extends AbstractController
{
   
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
    $this->doctrine = $doctrine;
     }

    public function index()
    {
        
        
        $Manager = $this->doctrine->getManager();
        return $this->render('my_agendamj\index.html.twig');
    }
}
