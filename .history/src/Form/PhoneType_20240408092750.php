<?php

namespace App\Form;

use App\Entity\Agenda\Phone;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
    ->add('number', TextType::class, [
        'attr' => [
            'class' => 'form-control',
            'placeholder' => 'Enter phone number',
        ],
    ])
    ->add('type', ChoiceType::class, [
        'choices' => [
            'TYPE OF PHONE' => [
                '' => '',
                'Office' => 'Office',
                'Home' => 'Home',
                'Personal' => 'Personal',
            ],
        ],
        'attr' => [
            'class' => 'form-control',
        ],
        'label_attr' => [
            'class' => 'control-label',
        ],
    ])
    ->add('save', SubmitType::class, [
        'attr' => [
            'class' => 'btn btn-primary',
            'id' => 'btn btn-primary',
        ],
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
        ]);
    }
}
