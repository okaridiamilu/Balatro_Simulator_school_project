<?php

namespace App\Form;

use App\Enum\RareteJoker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de filtre pour les JokerTemplate
 * Note: Le champ "etat" a été retiré car il appartient désormais à JokerInstance
 */
class JokerFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Construire les choix de rareté à partir de l'ENUM
        $raretes = ['Toutes' => ''];
        foreach (RareteJoker::cases() as $rarete) {
            $raretes[ucfirst($rarete->value)] = $rarete->value;
        }
        
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Rechercher par nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom du joker...',
                    'class' => 'form-control'
                ]
            ])
            ->add('rarete', ChoiceType::class, [
                'label' => 'Filtrer par rareté',
                'choices' => $raretes,
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
