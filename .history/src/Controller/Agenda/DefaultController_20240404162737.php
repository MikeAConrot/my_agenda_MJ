<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


public function new(Request $request)
{   
    $contact = new Contact();
    $form = $this->createForm(ContactType::class, $contact, array(
        'action' => $this->generateUrl('contact_new'),
        'method' => 'POST'
    ));

    return $this->render('Agenda\Contact\index.html.twig',[
        'form' => $form->createView(),
    ]);
}
}
