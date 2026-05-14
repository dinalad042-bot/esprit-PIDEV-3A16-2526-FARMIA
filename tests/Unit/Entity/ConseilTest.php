<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Conseil;
use PHPUnit\Framework\TestCase;

class ConseilTest extends TestCase
{
    public function testConseilCreation(): void
    {
        $conseil = new Conseil();
        $conseil->setTitre('Rotation des cultures');
        
        $this->assertEquals('Rotation des cultures', $conseil->getTitre());
    }
}