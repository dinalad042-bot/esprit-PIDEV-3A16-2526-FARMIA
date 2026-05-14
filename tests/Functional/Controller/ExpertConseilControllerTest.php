<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Analyse;
use App\Entity\Conseil;
use App\Entity\User;
use App\Entity\Ferme;
use App\Enum\PrioriteConseil;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for ExpertConseilController
 *
 * @covers \App\Controller\Web\ExpertConseilController
 */
class ExpertConseilControllerTest extends BaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsExpert();
    }

    /**
     * TEST: Expert conseil list loads
     */
    public function testExpertConseilListLoads(): void
    {
        $this->client->request('GET', '/expert/conseil');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Conseil detail page loads
     */
    public function testConseilShowPageLoads(): void
    {
        $conseil = $this->createConseil();

        $this->client->request('GET', '/expert/conseil/' . $conseil->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Create conseil page loads
     */
    public function testCreateConseilPageLoads(): void
    {
        $analyse = $this->createAnalyse();

        $this->client->request('GET', '/expert/conseil/new/' . $analyse->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Edit conseil page loads
     */
    public function testEditConseilPageLoads(): void
    {
        $conseil = $this->createConseil();

        $this->client->request('GET', '/expert/conseil/' . $conseil->getId() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Delete conseil
     */
    public function testCanDeleteConseil(): void
    {
        $conseil = $this->createConseil();
        $id = $conseil->getId();

        $this->client->request('POST', '/expert/conseil/' . $id . '/delete', [
            '_token' => 'test_token'
        ]);

        $this->assertResponseRedirects();

        // Verify deletion
        $deletedConseil = $this->entityManager->getRepository(Conseil::class)->find($id);
        $this->assertNull($deletedConseil);
    }

    /**
     * TEST: Non-expert cannot access expert conseil routes
     */
    public function testNonExpertCannotAccessExpertConseilRoutes(): void
    {
        $this->logout();
        $this->loginAsUser();

        $this->client->request('GET', '/expert/conseil');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Helper: Create an analyse for testing
     */
    private function createAnalyse(): Analyse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);

        $ferme = new Ferme();
        $ferme->setNomFerme('Test Farm');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100);
        $ferme->setProprietaire($user);

        $analyse = new Analyse();
        $analyse->setDateAnalyse(new \DateTime());
        $analyse->setResultatTechnique('Test result');
        $analyse->setFerme($ferme);

        $this->entityManager->persist($ferme);
        $this->entityManager->persist($analyse);
        $this->entityManager->flush();

        return $analyse;
    }

    /**
     * Helper: Create a conseil for testing
     */
    private function createConseil(): Conseil
    {
        $analyse = $this->createAnalyse();

        $conseil = new Conseil();
        $conseil->setDescriptionConseil('Test conseil description');
        $conseil->setPriorite(PrioriteConseil::HAUTE);
        $conseil->setAnalyse($analyse);

        $this->entityManager->persist($conseil);
        $this->entityManager->flush();

        return $conseil;
    }
}
