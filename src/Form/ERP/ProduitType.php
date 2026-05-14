<?php

namespace App\Form\ERP;

use App\Entity\ERP\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProduitType extends AbstractType
{
    private function num(array $extra = []): array
    {
        return array_merge(['scale' => 2, 'grouping' => false, 'rounding_mode' => \NumberFormatter::ROUND_HALFUP], $extra);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom du produit', 'constraints' => [new NotBlank()]])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('prixVente', NumberType::class, $this->num(['label' => 'Prix de vente (€)', 'constraints' => [new PositiveOrZero()]]))
            ->add('isSimple', CheckboxType::class, [
                'label' => 'Produit simple (vendu directement, sans recette — ex: œufs, lait)',
                'required' => false,
            ])
            ->add('stock', NumberType::class, $this->num([
                'label' => 'Stock initial (pour produit simple)',
                'required' => false,
                'constraints' => [new PositiveOrZero()],
            ]))
            ->add('quantiteProduite', NumberType::class, $this->num([
                'label' => 'Quantité produite par recette (ex: 2 chaises par lot)',
                'constraints' => [new Positive()],
                'attr' => ['min' => 0.01, 'step' => '0.01'],
            ]))
            ->add('recette', CollectionType::class, [
                'entry_type' => RecetteIngredientType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Produit::class]);
    }
}
