<?php

namespace App\Controller\Agenda;

use App\Entity\Agenda\Contact;
use App\Form\ContactType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    private function handleContactForm(Request $request, Contact $contact = null) 
    {
        $manager = $this->doctrine->getManager();
        $form = $this->createForm(ContactType::class, $contact, array(
            'action' => $this->generateUrl('new_contact'),
            'method' => 'POST'
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();
            $contact->setUser($this->getUser());
            $manager->persist($contact);
            $manager->flush();
            return $this->redirectToRoute('contact_by_user');
        }
        return $this->render('AgendaBundle:Contact:new_contact.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function newContactAction(Request $request)
    {
        $contact = new Contact();
        return $this->handleContactForm($request, $contact);
    }

    public function new(Request $request)
    {
        $contact = new Contact();
        return $this->handleContactForm($request, $contact);
    }

    public function show_contact()
    {
        $manager = $this->doctrine->getManager();
        $contacts = $manager->getRepository(Contact::class)->findAll();
        return $this->render('my_agendamj\Contact\showcontact.html.twig', array(
            'contacts' => $contacts,
        ));
    }

    public function edit_contacts(Request $request, $idContact)
    {
        $manager = $this->doctrine->getManager();
        $contact = $manager->getRepository(Contact::class)->find($idContact);
        $form = $this->createForm(ContactType::class, $contact, array(
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isRequired()) {
            $manager->persist($contact);
            $manager->flush();
            return $this->redirectToRoute('show_contacts');
        }
        return $this->render('my_agendamj\Contact\editcontact.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function delete_contacts($idContact)
    {
        $manager = $this->doctrine->getManager();
        $contact = $manager->getRepository(Contact::class)->find($idContact);
        $manager->remove($contact);
        $manager->flush();
        return $this->redirectToRoute('show_contacts');
    }

    public function getContactByUserAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $manager = $this->doctrine->getManager();
        $contactRepository = $manager->getRepository('AgendaBundle:Contact');
        $contactQuery = $contactRepository->createQueryBuilder('c')
            ->select('c')
            ->where('c.user = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()->getResult();
        return $this->render('AgendaBundle:Contact:contacts_by_user.html.twig', array(
            'Contacts' => $contactQuery
        ));
    }
}