<?php

namespace App\Controller\Agenda;


use App\Entity\Agenda\Contact;
use App\Form\ContactType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
   

    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine = $doctrine;
    }
    public function new(Request $request) 
    {
        
        $manager= $this->doctrine->getManager();

        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact, array(
            'action' => $this->generateUrl('contact_new'),
            'method' => 'POST'
        ));
 

        $form->handleRequest($request);

        //VALIDACION
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->persist($contact); //Agregar informacion en la memoria del Manager
            $manager->flush();//procesar antualizacion o inserciones de datos
  
            //Retornar a la ruta principal si es correcto todo
            return $this->redirectToRoute('index');
        }


            return $this->render('my_agendamj\Contact\contactos.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa'
        ]);
    } 


    public function index(){
        //Manager de Doctine(Base de datos )
        $manager = $this->doctrine->getManager();
         //Consulta y objeto sobre contacto 
        $contacts = $manager ->getRepository(Contact::class)->findAll();
        //renderizar Vista
        return $this->render('Agenda\Contact\index.html.twig',[
             //Consulta de Contactos
             'contacts' => $contacts
        ]);
    }

}
