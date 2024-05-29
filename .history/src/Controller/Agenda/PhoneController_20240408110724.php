<?php

namespace App\Controller\Agenda;

use App\Entity\Agenda\Phone;
use App\Form\PhoneType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class PhoneController extends AbstractController
{
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine = $doctrine;
    }


    //funcion paRA AGREGAR TELEFONOS Y RENDER EL FORM. 
    public function phones(Request $request)
    {

        $manager= $this->doctrine->getManager();

        $phone = new Phone();

        $form = $this->createForm(PhoneType::class, $phone, array(
            'action' => $this->generateUrl('phones_new'),
            'method' => 'POST'
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->persist($phone); //Agregar informacion en la memoria del Manager
            $manager->flush();//procesar antualizacion o inserciones de datos
  
            //Retornar a la ruta principal si es correcto todo
            return $this->redirectToRoute('index');
        }

        return $this->render('my_agendamj\Contact\phones.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa']);
        //return $this->render('my_agendamj/Contact/phones.html.twig');
    }


    //FUNCION PARA MOSTRAR TELEFONOS
    public function show_phones(){
        //Manager de Doctine(Base de datos )
        $manager = $this->doctrine->getManager();
         //Consulta y objeto sobre contacto 
        $phones = $manager ->getRepository(Phone::class)->findAll();

        //renderizar Vista
        return $this->render('my_agendamj\Contact\showphones.html.twig',[
             //Consulta de Contactos
             'phones' => $phones,
        ]);
     }


     //FUNCION PARA EDITAR TELEFONOS. 
     public function edit_phones(Request $request, $idContact){
        //Manager de Doctine(Base de datos )
        $manager = $this->doctrine->getManager();
       

         //Consulta de CONTACTO Buscando por Id
         $phone = $manager->getRepository(Pho::class)->find($idContact);
         

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
