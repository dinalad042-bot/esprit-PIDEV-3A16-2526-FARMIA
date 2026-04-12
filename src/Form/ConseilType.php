<?php

namespace App\Form;

use App\Entity\Analyse;
use App\Entity\Conseil;
use App\Enum\Priorite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConseilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $analyseId = $options['analyse_id'] ?? null;

        $builder
            ->add('descriptionConseil', TextareaType::class, [
                'label' => 'Description du conseil',
                'attr'  => [
                    'class'       => 'form-control profile-input',
                    'rows'        => 4,
                    'placeholder' => 'Décrivez le conseil (min. 10 caractères)...',
                ],
            ])
            ->add('prioriteRaw', ChoiceType::class, [
                'label'   => 'Priorité',
                'choices' => [
                    '🔴 Haute'   => 'HAUTE',
                    '🟡 Moyenne' => 'MOYENNE',
                    '🟢 Basse'   => 'BASSE',
                ],
                'attr' => ['class' => 'form-control profile-input'],
            ])
            ->add('analyse', EntityType::class, [
                'class'        => Analyse::class,
                'choice_label' => fn(Analyse $a) =>
                    'Analyse #'.$a->getId()
                    .' — '.($a->getFerme()?->getNomFerme() ?? 'N/A')
                    .' ('.$a->getDateAnalyse()?->format('d/m/Y').')',
                'label'       => 'Analyse associée',
                'placeholder' => '-- Sélectionner une analyse --',
                'attr'        => ['class' => 'form-control profile-input'],
                'data'        => $analyseId ? null : null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conseil::class,
            'analyse_id' => null,
        ]);
    }
}