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

        //MANEJADOR DE ENTIDADES
        $manager = $this->doctrine->getManager();
        

        //ENTIDAD A REGISTRAR
        $contact = new Contact();

        //OBTENCION Y FORMATEO DEL FORM
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
