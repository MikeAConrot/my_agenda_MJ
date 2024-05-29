<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;


class PhoneController extends AbstractController
{
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine = $doctrine;
    }




    public function phones()
    {

        $manager= $this->doctrine->getManager();

        $contact = new Ct();









        return $this->render('my_agendamj/Contact/phones.html.twig');
    }
}
