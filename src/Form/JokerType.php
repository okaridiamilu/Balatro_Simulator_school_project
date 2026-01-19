<?php

namespace App\Form;

use App\Entity\Joker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JokerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Joker',
                'attr' => [
                    'placeholder' => 'Ex: Joker Vampire',
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Normale' => 'normale',
                    'Foil' => 'foil',
                    'Polychrome' => 'polychrome',
                    'Chromatique' => 'chromatique'
                ],
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionner un état',
                'required' => true
            ])
            ->add('rarete', ChoiceType::class, [
                'label' => 'Rareté',
                'choices' => [
                    'Commun' => 'commun',
                    'Uncommon' => 'uncommon',
                    'Rare' => 'rare',
                    'Legendary' => 'legendary'
                ],
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionner une rareté',
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du joker...',
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'required' => true
            ])
            ->add('effet', TextareaType::class, [
                'label' => 'Effet',
                'attr' => [
                    'placeholder' => 'Effet du joker en jeu...',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Joker::class,
        ]);
    }
}
