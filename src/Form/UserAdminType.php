<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['required' => false, 'attr' => ['class' => 'profile-input']])
            ->add('prenom', TextType::class, ['required' => false, 'attr' => ['class' => 'profile-input']])
            ->add('email', EmailType::class, ['required' => false, 'attr' => ['class' => 'profile-input']])
            ->add('cin', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'profile-input',
                    'inputmode' => 'numeric'
                ]
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'profile-input',
                    'inputmode' => 'numeric'
                ]
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'profile-input']
            ])
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'ADMIN' => 'ADMIN',
                    'EXPERT' => 'EXPERT',
                    'AGRICOLE' => 'AGRICOLE',
                    'FOURNISSEUR' => 'FOURNISSEUR',
                ],
                'attr' => ['class' => 'profile-input']
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'profile-input', 
                    'placeholder' => 'Laisser vide si pas de modif'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
