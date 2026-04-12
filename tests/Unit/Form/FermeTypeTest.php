<?php

namespace App\Tests\Unit\Form;

use App\Entity\Ferme;
use App\Form\FermeType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for FermeType form.
 *
 * @covers \App\Form\FermeType
 */
class FermeTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'nom_ferme' => 'Ma Ferme Test',
            'lieu' => 'Tunis',
            'surface' => 150.5,
            'latitude' => 36.8,
            'longitude' => 10.18,
        ];

        $ferme = new Ferme();
        $form = $this->factory->create(FermeType::class, $ferme);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('Ma Ferme Test', $ferme->getNomFerme());
        $this->assertEquals('Tunis', $ferme->getLieu());
        $this->assertEquals(150.5, $ferme->getSurface());
    }

    public function testFormFieldsExist(): void
    {
        $form = $this->factory->create(FermeType::class);
        
        $this->assertTrue($form->has('nom_ferme'), 'Form should have nom_ferme field');
        $this->assertTrue($form->has('lieu'), 'Form should have lieu field');
        $this->assertTrue($form->has('surface'), 'Form should have surface field');
        $this->assertTrue($form->has('latitude'), 'Form should have latitude field');
        $this->assertTrue($form->has('longitude'), 'Form should have longitude field');
    }

    public function testConfigureOptions(): void
    {
        $form = $this->factory->create(FermeType::class);
        $options = $form->getConfig()->getOptions();

        $this->assertEquals(Ferme::class, $options['data_class']);
    }
}
