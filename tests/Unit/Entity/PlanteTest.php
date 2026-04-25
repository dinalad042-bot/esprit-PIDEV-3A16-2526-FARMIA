<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Plante;
use App\Entity\Ferme;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Plante entity.
 *
 * @covers \App\Entity\Plante
 */
class PlanteTest extends TestCase
{
    public function testPlanteCreation(): void
    {
        $plante = new Plante();
        $this->assertInstanceOf(Plante::class, $plante);
    }
}
