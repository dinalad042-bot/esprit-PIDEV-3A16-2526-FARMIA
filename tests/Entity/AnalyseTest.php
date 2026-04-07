<?php

namespace App\Tests\Entity;

use App\Entity\Analyse;
use PHPUnit\Framework\TestCase;

class AnalyseTest extends TestCase
{
    public function testAnalyseEntity(): void
    {
        $analyse = new Analyse();
        $analyse->setType('PLANTE');
        $analyse->setSymptomes('Taches jaunes');
        $analyse->setResultat('Test Result');
        $analyse->setDate(new \DateTime('2026-04-07'));

        $this->assertEquals('PLANTE', $analyse->getType());
        $this->assertEquals('Taches jaunes', $analyse->getSymptomes());
        $this->assertEquals('Test Result', $analyse->getResultat());
        $this->assertEquals('2026-04-07', $analyse->getDate()->format('Y-m-d'));
    }
}
