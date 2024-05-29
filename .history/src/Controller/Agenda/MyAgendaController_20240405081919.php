<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MyAgendaController extends AbstractController
{
   
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
    $this->doctrine = $doctrine;
     }






    
    public function index()
    {
        
        
        $entityManager = $this->doctrine->getManager();
        return $this->render('my_agendamj\index.html.twig');
    }
}
