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
        self::$client->request('GET', '/expert/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    /**
     * TEST: Expert analyse list loads
     */
    public function testExpertAnalyseListLoads(): void
    {
        self::$client->request('GET', '/expert/analyses');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Pending analyses page loads
     */
    public function testPendingAnalysesPageLoads(): void
    {
        self::$client->request('GET', '/expert/demandes-en-attente');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Analyse detail page loads
     */
    public function testAnalyseShowPageLoads(): void
    {
        // Create an analyse
        $analyse = $this->createAnalyse();

        self::$client->request('GET', '/expert/analyse/' . $analyse->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Edit analyse page loads
     */
    public function testEditAnalysePageLoads(): void
    {
        $analyse = $this->createAnalyse();

        self::$client->request('GET', '/expert/analyse/' . $analyse->getId() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Delete analyse
     */
    public function testCanDeleteAnalyse(): void
    {
        $analyse = $this->createAnalyse();
        $id = $analyse->getId();

        self::$client->request('POST', '/expert/analyse/' . $id . '/delete', [
            '_token' => 'test_token'
        ]);

        $this->assertResponseRedirects();

        // Verify deletion
        $deletedAnalyse = self::$em->getRepository(Analyse::class)->find($id);
        $this->assertNull($deletedAnalyse, 'Analyse should be deleted but still exists');
    }

    /**
     * TEST: Update analyse status
     */
    public function testCanUpdateAnalyseStatus(): void
    {
        $analyse = $this->createAnalyse();

        self::$client->request('POST', '/expert/analyse/' . $analyse->getId() . '/status/en_cours', [
            '_token' => 'test_token'
        ]);

        $this->assertResponseRedirects();

        // Verify status update
        self::$em->refresh($analyse);
        $this->assertEquals('en_cours', $analyse->getStatut());
    }

    /**
     * TEST: Take request assigns expert
     */
    public function testTakeRequestAssignsExpert(): void
    {
        $analyse = $this->createAnalyse(StatutAnalyse::EN_ATTENTE);

        self::$client->request('POST', '/expert/demande/' . $analyse->getId() . '/prendre-en-charge', [
            '_token' => 'test_token'
        ]);

        $this->assertResponseRedirects();

        // Verify assignment
        self::$em->refresh($analyse);
        $this->assertNotNull($analyse->getTechnicien());
        $this->assertEquals('en_cours', $analyse->getStatut());
    }

    /**
     * TEST: Non-expert cannot access expert routes
     */
    public function testNonExpertCannotAccessExpertRoutes(): void
    {
        $this->logout();
        $this->loginAsUser();

        self::$client->request('GET', '/expert/dashboard');

        $this->assertResponseRedirects('/dashboard');
    }

    /**
     * TEST: Unauthenticated user is redirected
     */
    public function testUnauthenticatedUserIsRedirected(): void
    {
        $this->logout();

        self::$client->request('GET', '/expert/dashboard');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Helper: Create an analyse for testing
     */
    private function createAnalyse(StatutAnalyse $status = StatutAnalyse::EN_ATTENTE): Analyse
    {
        $user = self::$em->getRepository(User::class)->findOneBy([]);

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
        $analyse->setDemandeur($user);
        $analyse->setTechnicien($user);

        self::$em->persist($ferme);
        self::$em->persist($analyse);
        self::$em->flush();

        return $analyse;
    }
}
