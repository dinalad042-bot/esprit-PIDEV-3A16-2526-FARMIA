<?php 
namespace App\Tests\Unit\Entity;

use App\Entity\Plante;
use App\Entity\Ferme;
use PHPUnit\Framework\TestCase;

class PlanteTest extends TestCase
{
    public function testPlanteGetterSetter(): void
    {
        $plante = new Plante();
        $plante->setNom('Aloe Vera');
        
        $this->assertEquals('Aloe Vera', $plante->getNom());
    }
}