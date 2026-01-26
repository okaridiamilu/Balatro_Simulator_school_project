<?php

namespace App\Form;

use App\Entity\Joker;
use App\Enum\EtatJoker;
use App\Enum\RareteJoker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
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
            ->add('etat', EnumType::class, [
                'class' => EtatJoker::class,
                'label' => 'État',
                'choice_label' => fn(EtatJoker $etat) => ucfirst($etat->value),
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionner un état',
                'required' => true
            ])
            ->add('rarete', EnumType::class, [
                'class' => RareteJoker::class,
                'label' => 'Rareté',
                'choice_label' => fn(RareteJoker $rarete) => ucfirst($rarete->value),
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
            ->add('image', TextType::class, [
                'label' => 'URL de l\'image',
                'attr' => [
                    'placeholder' => 'Ex: /images/joker-default.jpg',
                    'class' => 'form-control'
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Joker::class,
        ]);
    }
}
