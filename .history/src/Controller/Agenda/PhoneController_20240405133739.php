<?php

namespace App\Controller\Agenda;

use App\Entity\Agenda\Phone;
use App\Form\PhoneType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\BrowserKit\Request;

class PhoneController extends AbstractController
{
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine = $doctrine;
    }




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




        return $this->render('my_agendamj\Contact\contactos.html.twig',[
            'form' => $form->createView(),
            'hola'=> 'holasa'
        ]);




        //return $this->render('my_agendamj/Contact/phones.html.twig');
    }
}
