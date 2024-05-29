<?php

namespace App\Controller\Agenda;


use App\Entity\Agenda\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\BrowserKit\Request;

class DefaultController extends AbstractController
{
   
    public function contact(Request $request)
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
 
        //atrapar respuesta del form
        $form->handleRequest($request);
       
       
        return $this->render('my_agendamj\contactos.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa'
        ]);


        

        
    }

  
}
