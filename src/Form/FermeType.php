<?php

namespace App\Form;

use App\Entity\Ferme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FermeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_ferme', TextType::class, [
                'label' => 'Nom de la Ferme',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Domaine de l\'Olivier'
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Localisation (Ville)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Bizerte, Tunisie'
                ]
            ])
            ->add('surface', NumberType::class, [
                'label' => 'Surface (Hectares)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 15.5'
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude GPS',
                'required' => false,
                'scale' => 8, // Précision des coordonnées
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 37.2744'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude GPS',
                'required' => false,
                'scale' => 8,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 9.8739'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ferme::class,
        ]);
    }
}