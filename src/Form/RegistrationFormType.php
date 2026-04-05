<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('cin', TextType::class, [
                'required' => false,
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required' => false,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe est obligatoire.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'Agricole' => 'ROLE_AGRICOLE',
                    'Expert' => 'ROLE_EXPERT',
                    'Fournisseur' => 'ROLE_FOURNISSEUR',
                    'Utilisateur (Défaut)' => 'ROLE_USER',
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le rôle est obligatoire.',
                    ])
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation.',
                    ]),
                ],
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
