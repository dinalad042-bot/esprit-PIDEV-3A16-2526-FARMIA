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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'profile-input'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'profile-input'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => ['class' => 'profile-input'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Email(['message' => 'Veuillez saisir une adresse email valide.']),
                ],
            ])
            ->add('cin', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'profile-input',
                    'inputmode' => 'numeric'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le CIN est obligatoire.']),
                    new Length(['exactly' => 8, 'exactMessage' => 'Le CIN doit contenir exactement 8 caractères.']),
                ],
            ])
            ->add('telephone', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'profile-input',
                    'inputmode' => 'numeric'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le téléphone est obligatoire.']),
                    new Length(['exactly' => 8, 'exactMessage' => 'Le téléphone doit contenir exactement 8 caractères.']),
                ],
            ])
            ->add('adresse', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'profile-input'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse est obligatoire.']),
                ],
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
