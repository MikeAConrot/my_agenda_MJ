<?php

namespace App\Controller\Agenda;


use App\Entity\Agenda\Contact;
use App\Form\ContactType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;


class DefaultController extends AbstractController
{
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
    $this->doctrine = $doctrine;
     }

   
    public function contact()
    {

       
        return $this->render('my_agendamj\contactos.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa'
        ]);


       





        
    }

    

  
}
