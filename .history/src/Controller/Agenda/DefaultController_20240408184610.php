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
        if ($form->isSubmitted() && $form->isRequired()) {
            $contact->setUsers($this->getUser()); /////////////////////
            $manager->persist($contact); //Agregar informacion en la memoria del Manager
            $manager->flush();//procesar antualizacion o inserciones de datos
  
            //Retornar a la ruta principal si es correcto todo
            return $this->redirectToRoute('show_contacts');
        }



            return $this->render('my_agendamj\Contact\contactos.html.twig',[
            'form' => $form->createView(),
            
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


     public function delete_contacts($idContact){
        //Manager de doctrine
        $manager = $this->doctrine->getManager();
        //Consulta de Contacto por id
        $contact = $manager->getRepository(Contact::class)->find($idContact);
        //Eliminacion de Registro
        $manager->remove($contact);
        //Enviar datos para eliminar
        $manager->flush();
          
        //Retornar ruta que muestra todos los contactos
        return $this->redirectToRoute('show_contacts');
     }


     public function show_contact()
    {
        //siempre se debe de estar logueado para utilizar la función
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
 
        $manager= $this->doctrine->getManager();
        $contactRepository = $manager->getRepository(Contact::class);
         
        $contactQuery = $contactRepository->createQueryBuilder('c')
                ->select('c')
                ->where('c.users = :users')
                ->setParameter('users', $this->getUsers($))
                ->getQuery()->getResult();
         
        return $this->render('my_agendamj\Contact\showcontact.html.twig', array(
            'Contacts' => $contactQuery
        ));
    }



}
