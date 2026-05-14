<?php

namespace App\Form\ERP;

use App\Entity\ERP\ListeVente;
use App\Entity\ERP\ServiceERP;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ListeVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => ServiceERP::class,
                'choice_label' => fn(ServiceERP $s) => $s->getNom() . ' (Stock: ' . $s->getStock() . ')',
                'label' => 'Service',
                'attr' => [
                    'class' => 'service-select',
                    'data-stock' => '0',
                ],
                'choice_attr' => fn(ServiceERP $s) => ['data-stock' => $s->getStock(), 'data-prix' => $s->getPrix()],
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
                'html5' => true,
                'attr' => ['min' => 0, 'class' => 'prix-input', 'step' => '0.01'],
                'constraints' => [new PositiveOrZero()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ListeVente::class]);
    }
}
