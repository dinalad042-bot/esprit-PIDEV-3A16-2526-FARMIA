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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = !$builder->getData() || !$builder->getData()->getId();

        $builder
            ->add('nom', TextType::class, ['required' => true, 'attr' => ['class' => 'profile-input']])
            ->add('prenom', TextType::class, ['required' => true, 'attr' => ['class' => 'profile-input']])
            ->add('email', EmailType::class, ['required' => true, 'attr' => ['class' => 'profile-input']])
            ->add('cin', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'profile-input',
                    'inputmode' => 'numeric',
                    'maxlength' => '8',
                    'pattern' => '\d{8}'
                ]
            ])
            ->add('telephone', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'profile-input',
                    'inputmode' => 'numeric',
                    'maxlength' => '8',
                    'pattern' => '\d{8}'
                ]
            ])
            ->add('adresse', TextType::class, [
                'required' => true,
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
            ]);

        $passwordConstraints = [
            new Length([
                'min' => 6,
                'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                'max' => 4096,
            ]),
        ];

        if ($isNew) {
            $passwordConstraints[] = new NotBlank([
                'message' => 'Le mot de passe est obligatoire pour un nouvel utilisateur.',
            ]);
        }

        $builder->add('password', PasswordType::class, [
            'mapped' => false,
            'required' => $isNew,
            'attr' => [
                'class' => 'profile-input', 
                'placeholder' => 'Laisser vide si pas de modif'
            ],
            'constraints' => $passwordConstraints,
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
