<?php

namespace App\Controller\Agenda;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PhoneController extends AbstractController
{
    
    public function index()
    {
        return $this->render('/m/phones.html.twig', [
            'controller_name' => 'PhoneController',
        ]);
    }
}
