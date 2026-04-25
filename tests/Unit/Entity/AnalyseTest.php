<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Analyse;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Analyse entity.
 *
 * @covers \App\Entity\Analyse
 */
class AnalyseTest extends TestCase
{
    public function testAnalyseCreation(): void
    {
        $analyse = new Analyse();
        $this->assertInstanceOf(Analyse::class, $analyse);
    }
}
