<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PhoneController extends AbstractController
{
    
    public function index()
    {
        return $this->render('agenda/phone/phones.html.twig', [
            'controller_name' => 'PhoneController',
        ]);
    }
}
