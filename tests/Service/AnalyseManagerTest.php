<?php

namespace App\Tests\Service;

use App\Entity\Analyse;
use App\Service\AnalyseManager;
use PHPUnit\Framework\TestCase;

class AnalyseManagerTest extends TestCase
{
    // Test 1 : Analyse valide
    public function testAnalyseValide(): void
    {
        $analyse = new Analyse();
        $analyse->setDescriptionDemande('Taches brunes sur les feuilles');
        $analyse->setStatut('en_attente');

        $manager = new AnalyseManager();
        $this->assertTrue($manager->validate($analyse));
    }

    // Test 2 : Description vide → exception
    public function testDescriptionVideLanceException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La description de la demande est obligatoire');

        $analyse = new Analyse();
        $analyse->setDescriptionDemande('');
        $analyse->setStatut('en_attente');

        $manager = new AnalyseManager();
        $manager->validate($analyse);
    }

    // Test 3 : Statut invalide → exception
    public function testStatutInvalideLanceException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut est invalide');

        $analyse = new Analyse();
        $analyse->setDescriptionDemande('Problème sur les tomates');
        $analyse->setStatut('statut_inexistant');

        $manager = new AnalyseManager();
        $manager->validate($analyse);
    }

    // Test 4 : Score de confiance invalide → exception
    public function testScoreConfianceInvalideLanceException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le score de confiance doit être entre 0 et 100');

        $analyse = new Analyse();
        $analyse->setDescriptionDemande('Problème détecté');
        $analyse->setStatut('en_cours');
        $analyse->setAiConfidenceScore('150');

        $manager = new AnalyseManager();
        $manager->validate($analyse);
    }

    // Test 5 : Score de confiance valide (92%)
    public function testScoreConfianceValide(): void
    {
        $analyse = new Analyse();
        $analyse->setDescriptionDemande('Diagnostic IA effectué');
        $analyse->setStatut('en_cours');
        $analyse->setAiConfidenceScore('92');

        $manager = new AnalyseManager();
        $this->assertTrue($manager->validate($analyse));
    }
}
