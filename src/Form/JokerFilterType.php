<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JokerFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Rechercher par nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom du joker...',
                    'class' => 'form-control'
                ]
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'Filtrer par état',
                'choices' => [
                    'Tous' => '',
                    'Normale' => 'normale',
                    'Foil' => 'foil',
                    'Polychrome' => 'polychrome',
                    'Chromatique' => 'chromatique'
                ],
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('rarete', ChoiceType::class, [
                'label' => 'Filtrer par rareté',
                'choices' => [
                    'Toutes' => '',
                    'Commun' => 'commun',
                    'Uncommon' => 'uncommon',
                    'Rare' => 'rare',
                    'Legendary' => 'legendary'
                ],
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false, // Pas nécessaire pour un filtre en GET
        ]);
    }
}
