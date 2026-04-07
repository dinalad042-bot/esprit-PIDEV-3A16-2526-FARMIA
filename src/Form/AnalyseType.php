<?php

namespace App\Form;

use App\Entity\Analyse;
use App\Entity\Ferme;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            ->add('technicien', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => fn(User $u) => $u->getPrenom().' '.$u->getNom().' ('.$u->getEmail().')',
                'query_builder' => fn($repo) => $repo->createQueryBuilder('u')
                    ->where("u.role IN ('EXPERT', 'ADMIN')")
                    ->orderBy('u.nom', 'ASC'),
                'label'       => 'Expert / Technicien',
                'placeholder' => '-- Sélectionner un expert --',
                'attr'        => ['class' => 'form-control profile-input'],
            ])
            ->add('ferme', EntityType::class, [
                'class'        => Ferme::class,
                'choice_label' => fn(Ferme $f) => $f->getNomFerme().' — '.$f->getLieu(),
                'label'        => 'Ferme',
                'placeholder'  => '-- Sélectionner une ferme --',
                'attr'         => ['class' => 'form-control profile-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Analyse::class]);
    }
}