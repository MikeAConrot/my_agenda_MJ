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
 
        //atrapar respuesta del form
        $form->handleRequest($request);
       
       
        return $this->render('my_agendamj\contactos.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa'
        ]);


          //SI el form es valido y se subio correctamente
       if ($form->isSubmitted() && $form->isValid()) {
            
        $manager->persist($contact); //Agregar informacion en la memoria del Manager
        $manager->flush();//procesar antualizacion o inserciones de datos

        //Retornar a la ruta principal si es correcto todo
        return $this->redirectToRoute('index');
       }

       return $this->render('Agenda\Contact\contactos.html.twig',[
        'form' => $form->createView(),]);
        






        
    }

    

  
}
