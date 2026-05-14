<?php

namespace App\Form\ERP;

use App\Entity\ERP\Matiere;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class MatiereType extends AbstractType
{
    private function num(array $extra = []): array
    {
        return array_merge(['scale' => 2, 'grouping' => false, 'rounding_mode' => \NumberFormatter::ROUND_HALFUP], $extra);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom', 'constraints' => [new NotBlank()]])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('unite', TextType::class, ['label' => 'Unité (kg, m, pièce…)'])
            ->add('prixUnitaire', NumberType::class, $this->num(['label' => 'Prix unitaire (€)', 'constraints' => [new PositiveOrZero()]]))
            ->add('stock', NumberType::class, $this->num(['label' => 'Stock initial', 'constraints' => [new PositiveOrZero()]]))
            ->add('seuilCritique', NumberType::class, $this->num(['label' => 'Seuil critique', 'constraints' => [new PositiveOrZero()]]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Matiere::class]);
    }
}
