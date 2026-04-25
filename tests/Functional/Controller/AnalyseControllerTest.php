<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Analyse;
use App\Entity\Ferme;
use App\Entity\User;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for AnalyseController.
 *
 * TEST: Analysis management by experts
 * Reason: Expert module requires analysis CRUD
 * Fat tail covered: Expert role required, ferme relationship
 *
 * @covers \App\Controller\AnalyseController
 */
class AnalyseControllerTest extends BaseWebTestCase
{
    public function testIndexPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        self::$client->request('GET', '/analyse');
        $this->assertResponseIsSuccessful();
    }

    public function testNewFormLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        self::$client->request('GET', '/analyse/new');
        $this->assertResponseIsSuccessful();
    }

    public function testCanCreateAnalyse(): void
    {
        $expert = $this->loginWithRole('ROLE_EXPERT');

        $ferme = $this->createTestFerme();
        self::$em->flush();

        // First, get the form to extract CSRF token
        $crawler = self::$client->request('GET', '/analyse/new');
        $form = $crawler->selectButton('Enregistrer')->form();

        // Submit the form with data
        self::$client->submit($form, [
            'analyse[ferme]' => $ferme->getIdFerme(),
            'analyse[technicien]' => $expert->getId(),
            'analyse[resultatTechnique]' => 'Test analysis results here',
            'analyse[dateAnalyse]' => '2024-01-15T10:00',
        ]);

        $this->assertResponseRedirects();

        $analyse = self::$em->getRepository(Analyse::class)->findOneBy(['resultatTechnique' => 'Test analysis results here']);
        $this->assertNotNull($analyse);
    }

    public function testShowPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        self::$em->flush();

        self::$client->request('GET', '/analyse/' . $analyse->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testEditPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        self::$em->flush();

        self::$client->request('GET', '/analyse/' . $analyse->getId() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testCanUpdateAnalyse(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        self::$em->flush();

        // Get the edit form with CSRF token
        $crawler = self::$client->request('GET', '/analyse/' . $analyse->getId() . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form();

        // Submit the form with updated data
        self::$client->submit($form, [
            'analyse[resultatTechnique]' => 'Updated analysis results',
            'analyse[dateAnalyse]' => '2024-02-20T10:00',
        ]);

        $this->assertResponseRedirects();
    }

    public function testCanDeleteAnalyse(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        self::$em->flush();
        $id = $analyse->getId();

        // CSRF protection is disabled in test environment, any token works
        self::$client->request('POST', '/analyse/' . $id . '/delete', ['_token' => 'dummy_token']);
        $this->assertResponseRedirects('/analyse');
    }

    public function testUnauthenticatedRedirect(): void
    {
        self::$client->request('GET', '/analyse/');
        $this->assertResponseRedirects();
    }

    private function createTestFerme(): Ferme
    {
        $ferme = new Ferme();
        $ferme->setNomFerme('Test Farm');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100.0);
        self::$em->persist($ferme);
        return $ferme;
    }

    private function createTestAnalyse(): Analyse
    {
        $ferme = $this->createTestFerme();
        $technicien = $this->createTestUser('ROLE_EXPERT');
        $demandeur = $this->createTestUser('ROLE_USER');

        $analyse = new Analyse();
        $analyse->setFerme($ferme);
        $analyse->setTechnicien($technicien);
        $analyse->setDemandeur($demandeur);
        $analyse->setResultatTechnique('Test analysis');
        $analyse->setDateAnalyse(new \DateTime());
        self::$em->persist($analyse);
        return $analyse;
    }
}
