<?php

namespace App\Form;

use App\Entity\JokerTemplate;
use App\Enum\RareteJoker;
use App\Enum\TypeStack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Formulaire pour créer un nouveau JokerTemplate (utilisé dans la route /joker/new du TP)
class JokerTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Joker',
                'attr' => [
                    'placeholder' => 'Ex: Vampire',
                    'class' => 'form-control'
                ],
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
                    'placeholder' => 'Ex: /images/joker-vampire.jpg',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('effetCode', TextType::class, [
                'label' => 'Code d\'effet',
                'attr' => [
                    'placeholder' => 'Ex: vampire, baron, constellation',
                    'class' => 'form-control'
                ],
                'help' => 'Identifiant unique pour la logique d\'effet',
                'required' => true
            ])
            ->add('typeStack', EnumType::class, [
                'class' => TypeStack::class,
                'label' => 'Type de stack',
                'choice_label' => fn(TypeStack $type) => match($type) {
                    TypeStack::CHIPS => 'Chips (+X)',
                    TypeStack::MULT_FLAT => 'Multiplicateur plat (+X)',
                    TypeStack::MULT_MULTIPLICATEUR => 'Multiplicateur multiplicatif (xX)',
                    TypeStack::XMULT => 'X Multiplicateur (xX)',
                },
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionner un type',
                'required' => true
            ])
            ->add('stackParUnite', NumberType::class, [
                'label' => 'Valeur par unité de stack',
                'attr' => [
                    'placeholder' => 'Ex: 15 pour +15 chips, 0.25 pour x0.25',
                    'class' => 'form-control',
                    'step' => '0.01'
                ],
                'help' => 'Valeur ajoutée par chaque stack',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JokerTemplate::class,
        ]);
    }
}
