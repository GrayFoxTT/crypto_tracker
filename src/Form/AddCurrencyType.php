<?php

// src/Form/AddCurrencyType.php
namespace App\Form;

use App\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddCurrencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('crypto', ChoiceType::class, [
                'placeholder' => 'Sélectionner une crypto',
                'choices' => [
                    'Bitcoin' => 'bitcoin',
                    'Ethereum' => 'ethereum',
                    'Ripple' => 'ripple',
                ],
            ])
            ->add('quantity')
            ->add('price')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Currency::class,
        ]);
    }
}
