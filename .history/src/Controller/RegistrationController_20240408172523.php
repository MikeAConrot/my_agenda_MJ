<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }


    public function getContactByUserAction() {
        //Llamada de doctrine
        $manager= $this->doctrine->getManager();
        //Objeto Contacto
        $contactRepository = $manager->getRepository(Contact::class);
        //Query de contacto
        // ->where, cláusula where de sql
        // ->setParameter, usada para pasar el parámetro con el que se va a consultar
        // ->getQuery para Obtener el query
        // ->getResult para obtener el resultado del query
        $contactQuery = $contactRepository->createQueryBuilder('c')
                ->select('c')
                ->where('c.user = :user')
                ->setParameter('user', $this->getUser())
                ->getQuery()->getResult();
         
        return $this->render('Agenda\Contact\index.html.twig', array(
            'Contacts' => $contactQuery
        ));
         
    }




}
