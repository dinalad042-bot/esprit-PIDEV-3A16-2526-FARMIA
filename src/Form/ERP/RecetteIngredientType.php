<?php

namespace App\Form\ERP;

use App\Entity\ERP\Matiere;
use App\Entity\ERP\RecetteIngredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matiere', EntityType::class, [
                'class' => Matiere::class,
                'choice_label' => fn(Matiere $m) => $m->getNom() . ' (stock: ' . $m->getStock() . ' ' . $m->getUnite() . ')',
                'label' => 'Matière',
                'attr' => ['class' => 'matiere-select'],
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité nécessaire',
                'scale' => 2,
                'grouping' => false,
                'rounding_mode' => \NumberFormatter::ROUND_HALFUP,
                'attr' => ['min' => 0.01, 'step' => '0.01', 'class' => 'ingredient-qty'],
                'constraints' => [new Positive()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RecetteIngredient::class]);
    }
}
