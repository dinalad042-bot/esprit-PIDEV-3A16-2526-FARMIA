<?php

namespace App\Form;

use App\Entity\Plante;
use App\Entity\Ferme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_espece', TextType::class, [
                'label' => 'Nom de l\'espèce',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Blé, Tomate, Olive'
                ]
            ])
            ->add('cycle_vie', TextType::class, [
                'label' => 'Cycle de vie',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Annuel, Biennal, Vivace'
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 100'
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
            'data_class' => Plante::class,
        ]);
    }
}