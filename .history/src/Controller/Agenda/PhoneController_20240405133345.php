<?php

namespace App\Controller\Agenda;

use App\Entity\Agenda\Phone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;


class PhoneController extends AbstractController
{
    
    private $doctrine;
    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine = $doctrine;
    }




    public function phones()
    {

        $manager= $this->doctrine->getManager();

        $phone = new Phone();

        $form = $this->createForm(ContactType::class, $phone, array(
            'action' => $this->generateUrl('contact_new'),
            'method' => 'POST'
        ));









        return $this->render('my_agendamj/Contact/phones.html.twig');
    }
}
