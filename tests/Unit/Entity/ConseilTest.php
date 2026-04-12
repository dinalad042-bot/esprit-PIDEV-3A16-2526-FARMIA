<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Conseil;
use App\Entity\Analyse;
use App\Enum\Priorite;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Conseil entity.
 *
 * @covers \App\Entity\Conseil
 */
class ConseilTest extends TestCase
{
    public function testGetId(): void
    {
        $conseil = new Conseil();
        $this->assertNull($conseil->getId());
    }

    public function testDescriptionConseil(): void
    {
        $conseil = new Conseil();
        $this->assertNull($conseil->getDescriptionConseil());

        $conseil->setDescriptionConseil('Test description');
        $this->assertEquals('Test description', $conseil->getDescriptionConseil());
    }

    public function testPrioriteEnum(): void
    {
        $conseil = new Conseil();

        $conseil->setPriorite(Priorite::HAUTE);
        $this->assertEquals(Priorite::HAUTE, $conseil->getPriorite());

        $conseil->setPriorite(Priorite::MOYENNE);
        $this->assertEquals(Priorite::MOYENNE, $conseil->getPriorite());

        $conseil->setPriorite(Priorite::BASSE);
        $this->assertEquals(Priorite::BASSE, $conseil->getPriorite());
    }

    public function testAnalyseRelationship(): void
    {
        $conseil = new Conseil();
        $analyse = new Analyse();

        $conseil->setAnalyse($analyse);
        $this->assertSame($analyse, $conseil->getAnalyse());

        $conseil->setAnalyse(null);
        $this->assertNull($conseil->getAnalyse());
    }

    public function testPrioriteRaw(): void
    {
        $conseil = new Conseil();
        
        $conseil->setPrioriteRaw('HAUTE');
        $this->assertEquals('HAUTE', $conseil->getPrioriteRaw());
    }
}
