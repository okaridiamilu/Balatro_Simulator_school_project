<?php

namespace App\Form;

use App\Entity\JokerInstance;
use App\Entity\JokerTemplate;
use App\Enum\EtatJoker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JokerInstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jokerTemplate', EntityType::class, [
                'class' => JokerTemplate::class,
                'choice_label' => function(JokerTemplate $template) {
                    return $template->getNom() . ' (' . ucfirst($template->getRarete()->value) . ')';
                },
                'label' => 'Sélectionner un Joker',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Choisir un joker...',
                'required' => true,
                'group_by' => function(JokerTemplate $template) {
                    return ucfirst($template->getRarete()->value);
                },
            ])
            ->add('etat', EnumType::class, [
                'class' => EtatJoker::class,
                'label' => 'État (optionnel)',
                'choice_label' => fn(EtatJoker $etat) => ucfirst($etat->value),
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Normal (aucun effet)',
                'required' => false,
                'help' => 'Les états spéciaux donnent des bonus supplémentaires'
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Position (1-5)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 5
                ],
                'required' => true,
                'help' => 'Position du joker dans votre rangée (1 à 5)'
            ])
            ->add('compteurStack', IntegerType::class, [
                'label' => 'Stacks de départ',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'value' => 0
                ],
                'required' => false,
                'help' => 'Nombre de stacks accumulés (généralement 0 au départ)',
                'data' => 0
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JokerInstance::class,
        ]);
    }
}
