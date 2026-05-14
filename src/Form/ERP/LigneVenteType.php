<?php

namespace App\Form\ERP;

use App\Entity\ERP\LigneVente;
use App\Entity\ERP\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class LigneVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => fn(Produit $p) => $p->getNom() . ' (stock: ' . $p->getStock() . ' — ' . number_format($p->getPrixVente(), 2) . ' €)',
                'choice_attr' => fn(Produit $p) => ['data-prix' => $p->getPrixVente()],
                'label' => 'Produit fini',
                'attr' => ['class' => 'produit-select'],
                'constraints' => [new NotNull()],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['min' => 1, 'class' => 'quantite-input'],
                'constraints' => [new Positive()],
            ])
            ->add('prixUnitaire', NumberType::class, [
                'label' => 'Prix unitaire (€)',
                'scale' => 2,
                'grouping' => false,
                'rounding_mode' => \NumberFormatter::ROUND_HALFUP,
                'attr' => ['min' => 0, 'step' => '0.01', 'class' => 'prix-input'],
                'constraints' => [new PositiveOrZero()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => LigneVente::class]);
    }
}
