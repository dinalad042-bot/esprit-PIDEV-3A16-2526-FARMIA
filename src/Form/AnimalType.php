<?php

namespace App\Form;

use App\Entity\Animal;
use App\Entity\Ferme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('espece', TextType::class, [
                'label' => 'Espèce',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Vache, Mouton, Poule'
                ]
            ])
            ->add('etat_sante', TextType::class, [
                'label' => 'État de santé',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Bon, À surveiller, Malade'
                ]
            ])
            ->add('date_naissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('ferme', EntityType::class, [
                'class' => Ferme::class,
                'choice_label' => 'nomFerme',
                'label' => 'Ferme',
                'attr' => [
                    'class' => 'form-control'
                ],
                'placeholder' => 'Sélectionnez une ferme'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}