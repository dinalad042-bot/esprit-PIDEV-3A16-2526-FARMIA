<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Analyse;
use App\Entity\User;
use App\Entity\Ferme;
use App\Enum\StatutAnalyse;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for ExpertAnalyseController
 *
 * @covers \App\Controller\Web\ExpertAnalyseController
 */
class ExpertAnalyseControllerTest extends BaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsExpert();
    }

    /**
     * TEST: Expert dashboard loads
     */
    public function testExpertDashboardLoads(): void
    {
        $this->client->request('GET', '/expert/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    /**
     * TEST: Expert analyse list loads
     */
    public function testExpertAnalyseListLoads(): void
    {
        $this->client->request('GET', '/expert/analyse');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Pending analyses page loads
     */
    public function testPendingAnalysesPageLoads(): void
    {
        $this->client->request('GET', '/expert/analyse/pending');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Analyse detail page loads
     */
    public function testAnalyseShowPageLoads(): void
    {
        // Create an analyse
        $analyse = $this->createAnalyse();

        $this->client->request('GET', '/expert/analyse/' . $analyse->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Edit analyse page loads
     */
    public function testEditAnalysePageLoads(): void
    {
        $analyse = $this->createAnalyse();

        $this->client->request('GET', '/expert/analyse/' . $analyse->getId() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Delete analyse
     */
    public function testCanDeleteAnalyse(): void
    {
        $analyse = $this->createAnalyse();
        $id = $analyse->getId();

        $this->client->request('POST', '/expert/analyse/' . $id . '/delete', [
            '_token' => 'test_token'
        ]);

        $this->assertResponseRedirects();

        // Verify deletion
        $deletedAnalyse = $this->entityManager->getRepository(Analyse::class)->find($id);
        $this->assertNull($deletedAnalyse);
    }

    /**
     * TEST: Update analyse status
     */
    public function testCanUpdateAnalyseStatus(): void
    {
        $analyse = $this->createAnalyse();

        $this->client->request('POST', '/expert/analyse/' . $analyse->getId() . '/status', [
            'status' => 'en_cours'
        ]);

        $this->assertResponseRedirects();

        // Verify status update
        $this->entityManager->refresh($analyse);
        $this->assertEquals(StatutAnalyse::EN_COURS, $analyse->getStatut());
    }

    /**
     * TEST: Take request assigns expert
     */
    public function testTakeRequestAssignsExpert(): void
    {
        $analyse = $this->createAnalyse(StatutAnalyse::EN_ATTENTE);

        $this->client->request('POST', '/expert/analyse/' . $analyse->getId() . '/take');

        $this->assertResponseRedirects();

        // Verify assignment
        $this->entityManager->refresh($analyse);
        $this->assertNotNull($analyse->getTechnicien());
        $this->assertEquals(StatutAnalyse::EN_COURS, $analyse->getStatut());
    }

    /**
     * TEST: Non-expert cannot access expert routes
     */
    public function testNonExpertCannotAccessExpertRoutes(): void
    {
        $this->logout();
        $this->loginAsUser();

        $this->client->request('GET', '/expert/');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * TEST: Unauthenticated user is redirected
     */
    public function testUnauthenticatedUserIsRedirected(): void
    {
        $this->logout();

        $this->client->request('GET', '/expert/');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Helper: Create an analyse for testing
     */
    private function createAnalyse(StatutAnalyse $status = StatutAnalyse::EN_ATTENTE): Analyse
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
        $analyse->setStatut($status);
        $analyse->setFerme($ferme);

        $this->entityManager->persist($ferme);
        $this->entityManager->persist($analyse);
        $this->entityManager->flush();

        return $analyse;
    }
}
