<?php

namespace App\Controller\Agenda;


use App\Entity\Agenda\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
   
    public function contact() 
    {
        
        $contact = new Contact();
        $form = $this->createForm(::class, $contact, array(
            'action' => $this->generateUrl('contact_new'),
            'method' => 'POST'
        ));
 
        return $this->render('Agenda\Contact\index.html.twig',[
            'form' => $form->createView(),
    }
}
