<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PhoneController extends AbstractController
{
    
    public function ()
    {
        return $this->render('/templates/my_agendamj/Contact/phones.html.twig', [
            'controller_name' => 'PhoneController',
        ]);
    }
}
