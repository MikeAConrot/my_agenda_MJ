<?php

namespace App\Form;

use App\Entity\Agenda\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use 

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label'=> 'Contact Name',
                 'label_attr'=>["class"=>"prueba"],
                'required'=>true, 
                'attr'=>["class"=>"input"]
            ])
            ->add('lastname')
            ->add('mail')
            ->add('save', SubmitType::class)


            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Main Statuses' => [
                        'Yes' => 'stock_yes',
                        'No' => 'stock_no',
                    ],
                    'Out of Stock Statuses' => [
                        'Backordered' => 'stock_backordered',
                        'Discontinued' => 'stock_discontinued',
                    ],
                ],
            ]);






    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
