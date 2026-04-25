<?php  
  
namespace App\Tests\Unit\Entity;  
  
use App\Entity\Conseil;  
use App\Entity\Analyse;  
use App\Enum\Priorite;  
use PHPUnit\Framework\TestCase;  
  
class ConseilTest extends TestCase  
{
    public function testConseilCreation(): void
    {
        $conseil = new Conseil();
        $this->assertInstanceOf(Conseil::class, $conseil);
    }
}
