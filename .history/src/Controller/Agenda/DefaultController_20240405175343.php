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


    public function show_contact(){
        //Manager de Doctine(Base de datos )
        $manager = $this->doctrine->getManager();
         //Consulta y objeto sobre contacto 
        $contacts = $manager ->getRepository(Contact::class)->findAll();
        //renderizar Vista
        return $this->render('my_agendamj\Contact\showcontact.html.twig',[
             //Consulta de Contactos
             'contacts' => $contacts,
             'form' => $form
        ]);
     }


     public function edit_contacts(Request $request, $idContact){
        //Manager de Doctine(Base de datos )
        $manager = $this->doctrine->getManager();
       

         //Consulta de CONTACTO Buscando por Id
         $contact = $manager->getRepository(Contact::class)->find($idContact);
         

          //Creación de Formulario para Editar(Es el mismo Formulario de Creación)
        //Pasar el Objeto $contactoRepository que contiene la info  del contacto a editar
         $form = $this->createForm(ContactType::class, $contact, array(
         
        ));

         $form->handleRequest($request);
         if($form->isSubmitted() && $form->isRequired()){
             //Agregar el Entitymanager y Enviar los datos a insertar
             $manager->persist($contact);
             $manager->flush();
             //Redirigir a la vista que quieras en caso que sea exitoso
             return $this->redirectToRoute('show_contacts');
         }

         return $this->render('my_agendamj\Contact\editcontact.html.twig',[
            'form' => $form->createView(),
        ]);
     }










}
