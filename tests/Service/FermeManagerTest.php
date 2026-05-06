<?php
namespace App\Tests\Service;

use App\Entity\Ferme;
use App\Service\FermeManager;
use PHPUnit\Framework\TestCase;

class FermeManagerTest extends TestCase 
{
    // CE TEST DOIT ÊTRE ROUGE SI TU EFFACES LES DONNÉES
    public function testValidFerme(): void
    {
        $ferme = new Ferme();
        $ferme->setNomFerme('Grande Ferme'); // SI TU EFFACES ÇA -> ÇA DOIT ÊTRE ROUGE
        $ferme->setSurface(500.5);           // SI TU METS 0 ICI -> ÇA DOIT ÊTRE ROUGE

        $manager = new FermeManager();
        
        // L'assertion : on affirme que validate() DOIT renvoyer true.
        // Si le manager lance une exception (car données vides), PHPUnit dira "Error/Failed".
        $this->assertTrue($manager->validate($ferme));
    }

    // CE TEST DOIT RESTER VERT SI LE NOM EST VIDE (car on attend l'erreur)
    public function testFermeWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $ferme = new Ferme();
        $ferme->setNomFerme(''); // On force le vide pour tester la sécurité
        $ferme->setSurface(100);

        $manager = new FermeManager();
        $manager->validate($ferme); 
    }
}