<?php

namespace App\Controller\Agenda;


use App\Entity\Agenda\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Persistence\ManagerRegistry;

class DefaultController extends AbstractController
{
   
    public function contact() 
    {


        //MANEJADOR DE ENTIDADES
        $manager = $this->doctrine->getManager();
        

        //ENTIDAD A REGISTRAR
        $contact = new Contact();

        //
        $form = $this->createForm(ContactType::class, $contact, array(
            'action' => $this->generateUrl('contact_new'),
            'method' => 'POST'
        ));
 
       
       
       
        return $this->render('my_agendamj\contactos.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa'
        ]);


        
    }

  
}
