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
        $this->client->request('GET', '/analyse/');
        $this->assertResponseIsSuccessful();
    }

    public function testNewFormLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $this->client->request('GET', '/analyse/new');
        $this->assertResponseIsSuccessful();
    }

    public function testCanCreateAnalyse(): void
    {
        $this->loginWithRole('ROLE_EXPERT');

        $ferme = $this->createTestFerme();
        $this->em->flush();

        $this->client->request('POST', '/analyse/new', [
            'ferme' => $ferme->getIdFerme(),
            'resultatTechnique' => 'Test analysis results here',
            'dateAnalyse' => '2024-01-15'
        ]);

        $this->assertResponseRedirects();

        $analyse = $this->em->getRepository(Analyse::class)->findOneBy(['resultatTechnique' => 'Test analysis results here']);
        $this->assertNotNull($analyse);
    }

    public function testShowPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        $this->em->flush();

        $this->client->request('GET', '/analyse/' . $analyse->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testEditPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        $this->em->flush();

        $this->client->request('GET', '/analyse/' . $analyse->getId() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testCanUpdateAnalyse(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        $this->em->flush();

        $this->client->request('POST', '/analyse/' . $analyse->getId() . '/edit', [
            'resultatTechnique' => 'Updated analysis results',
            'dateAnalyse' => '2024-02-20'
        ]);

        $this->assertResponseRedirects();
    }

    public function testCanDeleteAnalyse(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $analyse = $this->createTestAnalyse();
        $this->em->flush();
        $id = $analyse->getId();

        $token = $this->client->getContainer()->get('security.csrf.token_manager')
            ->getToken('delete' . $id)->getValue();

        $this->client->request('POST', '/analyse/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects('/analyse/');
    }

    public function testPdfGeneration(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $this->createTestAnalyse();
        $this->em->flush();

        $this->client->request('GET', '/analyse/pdf');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testUnauthenticatedRedirect(): void
    {
        $this->client->request('GET', '/analyse/');
        $this->assertResponseRedirects();
    }

    private function createTestFerme(): Ferme
    {
        $ferme = new Ferme();
        $ferme->setNomFerme('Test Farm');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100.0);
        $this->em->persist($ferme);
        return $ferme;
    }

    private function createTestAnalyse(): Analyse
    {
        $ferme = $this->createTestFerme();
        $technicien = $this->createTestUser('ROLE_EXPERT');

        $analyse = new Analyse();
        $analyse->setFerme($ferme);
        $analyse->setTechnicien($technicien);
        $analyse->setResultatTechnique('Test analysis');
        $analyse->setDateAnalyse(new \DateTime());
        $this->em->persist($analyse);
        return $analyse;
    }
}
