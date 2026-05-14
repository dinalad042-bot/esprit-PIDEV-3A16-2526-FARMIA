<?php

namespace App\Form\ERP;

use App\Entity\ERP\LigneAchat;
use App\Entity\ERP\Matiere;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class LigneAchatType extends AbstractType
{
    private function num(array $extra = []): array
    {
        return array_merge(['scale' => 2, 'grouping' => false, 'rounding_mode' => \NumberFormatter::ROUND_HALFUP], $extra);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matiere', EntityType::class, [
                'class' => Matiere::class,
                'choice_label' => fn(Matiere $m) => $m->getNom() . ' (' . $m->getUnite() . ')',
                'choice_attr' => fn(Matiere $m) => ['data-prix' => $m->getPrixUnitaire()],
                'label' => 'Matière première',
                'attr' => ['class' => 'matiere-select'],
                'constraints' => [new NotNull()],
            ])
            ->add('quantite', NumberType::class, $this->num([
                'label' => 'Quantité',
                'attr' => ['min' => 0.01, 'step' => '0.01', 'class' => 'quantite-input'],
                'constraints' => [new Positive()],
            ]))
            ->add('prixUnitaire', NumberType::class, $this->num([
                'label' => 'Prix unitaire (€)',
                'attr' => ['min' => 0, 'step' => '0.01', 'class' => 'prix-input'],
                'constraints' => [new PositiveOrZero()],
            ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => LigneAchat::class]);
    }
}
