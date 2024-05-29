<?php

namespace App\Form;

use App\Entity\Agenda\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label'=> 'Contact Name',
                'label_attr'=>["class"=>"prueba"],
                'required'=>true, 
                'attr'=>["class"=>"form-control"]
            ])
            ->add('lastname', TextType::class,[
                'required'=>true, 
                'attr'=>["class"=>"form-control"]
            ])
            ->add('mail', EmailType::class,[
                'required'=>true, 
                'attr'=>["class"=>"form-control"]
            ])
            ->add('phones', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => PhoneType::class,
                'allow_add' => true,
                'by_reference' => false,                
            ])
            ->add('save', SubmitType::class,[
                'attr'=>["class"=>"btn btn-primary"]
            ]);
            
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
