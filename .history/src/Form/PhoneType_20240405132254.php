<?php

namespace App\Form;

use App\Entity\Agenda\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number')
            ->add('type', ChoiseType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
        ]);
    }
}
