<?php

namespace App\Form;

use App\Entity\Analyse;
use App\Entity\Animal;
use App\Entity\Ferme;
use App\Entity\Plante;
use App\Entity\User;
use App\Enum\StatutAnalyse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnalyseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateAnalyse', DateTimeType::class, [
                'label'  => "Date de l'analyse",
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control profile-input'],
            ])
            ->add('resultatTechnique', TextareaType::class, [
                'label'    => 'Résultat technique',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control profile-input',
                    'rows'        => 5,
                    'placeholder' => 'Décrivez le diagnostic technique...',
                ],
            ])
            ->add('imageUrl', TextType::class, [
                'label'    => "URL de l'image",
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control profile-input',
                    'placeholder' => 'https://example.com/image.jpg',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => StatutAnalyse::EN_ATTENTE->value,
                    'En cours' => StatutAnalyse::EN_COURS->value,
                    'Terminée' => StatutAnalyse::TERMINEE->value,
                    'Annulée' => StatutAnalyse::ANNULEE->value,
                ],
                'label' => 'Statut',
                'placeholder' => '-- Sélectionner un statut --',
                'attr' => ['class' => 'form-control profile-input'],
            ])
            ->add('descriptionDemande', TextareaType::class, [
                'label' => 'Description de la demande',
                'required' => false,
                'attr' => [
                    'class' => 'form-control profile-input',
                    'rows' => 3,
                    'placeholder' => 'Décrivez votre demande d\'analyse...',
                ],
            ])
            // Note: technicien is auto-set in controller for expert context
            // This field is only shown in admin context
            ->add('ferme', EntityType::class, [
                'class'        => Ferme::class,
                'choice_label' => fn(Ferme $f) => $f->getNomFerme().' — '.$f->getLieu(),
                'label'        => 'Ferme',
                'placeholder'  => '-- Sélectionner une ferme --',
                'attr'         => ['class' => 'form-control profile-input'],
            ])
            ->add('animalCible', EntityType::class, [
                'class' => Animal::class,
                'choice_label' => 'espece',
                'label' => 'Animal cible (optionnel)',
                'placeholder' => '-- Sélectionner un animal --',
                'required' => false,
                'attr' => ['class' => 'form-control profile-input'],
            ])
            ->add('planteCible', EntityType::class, [
                'class' => Plante::class,
                'choice_label' => 'nomEspece',
                'label' => 'Plante cible (optionnel)',
                'placeholder' => '-- Sélectionner une plante --',
                'required' => false,
                'attr' => ['class' => 'form-control profile-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Analyse::class]);
    }
}