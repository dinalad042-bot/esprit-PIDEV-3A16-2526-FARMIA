<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Analyse;
use App\Entity\Conseil;
use App\Entity\Ferme;
use App\Enum\Priorite;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for ConseilController.
 *
 * TEST: Advice/Recommendation management linked to analyses
 * Reason: Conseils provide expert recommendations based on analyses
 * Fat tail covered: Priorite enum, analyse relationship required
 *
 * @covers \App\Controller\ConseilController
 */
class ConseilControllerTest extends BaseWebTestCase
{
    public function testIndexPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        self::$client->request('GET', '/conseil/');
        $this->assertResponseIsSuccessful();
    }

    public function testNewFormLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        self::$client->request('GET', '/conseil/new');
        $this->assertResponseIsSuccessful();
    }

    public function testCanCreateConseil(): void
    {
        $this->loginWithRole('ROLE_EXPERT');

        $analyse = $this->createTestAnalyse();
        self::$em->flush();

        self::$client->request('POST', '/conseil/new', [
            'analyse' => $analyse->getId(),
            'descriptionConseil' => 'Test recommendation for the farm',
            'priorite' => Priorite::HAUTE->value
        ]);

        $this->assertResponseRedirects();

        $conseil = self::$em->getRepository(Conseil::class)->findOneBy(['descriptionConseil' => 'Test recommendation for the farm']);
        $this->assertNotNull($conseil);
    }

    public function testShowPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $conseil = $this->createTestConseil();
        self::$em->flush();

        self::$client->request('GET', '/conseil/' . $conseil->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testEditPageLoads(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $conseil = $this->createTestConseil();
        self::$em->flush();

        self::$client->request('GET', '/conseil/' . $conseil->getId() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testCanUpdateConseil(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $conseil = $this->createTestConseil();
        self::$em->flush();

        self::$client->request('POST', '/conseil/' . $conseil->getId() . '/edit', [
            'descriptionConseil' => 'Updated recommendation',
            'priorite' => Priorite::MOYENNE->value
        ]);

        $this->assertResponseRedirects();
    }

    public function testCanDeleteConseil(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $conseil = $this->createTestConseil();
        self::$em->flush();
        $id = $conseil->getId();

        $token = self::$client->getContainer()->get('security.csrf.token_manager')
            ->getToken('delete' . $id)->getValue();

        self::$client->request('POST', '/conseil/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects('/conseil/');
    }

    public function testUnauthenticatedRedirect(): void
    {
        self::$client->request('GET', '/conseil/');
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

    private function createTestAnalyse(): \App\Entity\Analyse
    {
        $ferme = $this->createTestFerme();
        $technicien = $this->createTestUser('ROLE_EXPERT');

        $analyse = new Analyse();
        $analyse->setFerme($ferme);
        $analyse->setTechnicien($technicien);
        $analyse->setResultatTechnique('Test analysis for conseil');
        $analyse->setDateAnalyse(new \DateTime());
        self::$em->persist($analyse);
        return $analyse;
    }

    private function createTestConseil(): Conseil
    {
        $analyse = $this->createTestAnalyse();
        $conseil = new Conseil();
        $conseil->setAnalyse($analyse);
        $conseil->setDescriptionConseil('Test recommendation');
        $conseil->setPriorite(Priorite::HAUTE);
        $conseil->setDate(new \DateTime());
        self::$em->persist($conseil);
        return $conseil;
    }
}