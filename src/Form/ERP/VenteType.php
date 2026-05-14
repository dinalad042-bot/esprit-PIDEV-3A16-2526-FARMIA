<?php

namespace App\Form\ERP;

use App\Entity\ERP\Vente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotNull;

class VenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateVente', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de vente',
                'constraints' => [new NotNull()],
            ])
            ->add('lignes', CollectionType::class, [
                'entry_type' => LigneVenteType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'constraints' => [new Count(min: 1, minMessage: 'Ajoutez au moins une ligne.')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Vente::class]);
    }
}
