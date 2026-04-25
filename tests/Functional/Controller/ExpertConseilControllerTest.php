<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Analyse;
use App\Entity\Conseil;
use App\Entity\User;
use App\Entity\Ferme;
use App\Enum\Priorite;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for ExpertConseilController
 *
 * @covers \App\Controller\Web\ExpertConseilController
 */
class ExpertConseilControllerTest extends BaseWebTestCase
{
    private ?User $expert = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expert = $this->loginAsExpert();
    }

    /**
     * TEST: Expert conseil list loads
     */
    public function testExpertConseilListLoads(): void
    {
        self::$client->request('GET', '/expert/conseils');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Conseil detail page loads
     */
    public function testConseilShowPageLoads(): void
    {
        $conseil = $this->createConseil();

        self::$client->request('GET', '/expert/conseil/' . $conseil->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Create conseil page loads
     */
    public function testCreateConseilPageLoads(): void
    {
        $analyse = $this->createAnalyse();

        self::$client->request('GET', '/expert/conseil/new');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Edit conseil page loads
     */
    public function testEditConseilPageLoads(): void
    {
        $conseil = $this->createConseil();

        self::$client->request('GET', '/expert/conseil/' . $conseil->getId() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Delete conseil
     */
    public function testCanDeleteConseil(): void
    {
        $conseil = $this->createConseil();
        $id = $conseil->getId();

        self::$client->request('POST', '/expert/conseil/' . $id . '/delete', [
            '_token' => 'test_token'
        ]);

        $this->assertResponseRedirects();

        // Verify deletion
        $deletedConseil = self::$em->getRepository(Conseil::class)->find($id);
        $this->assertNull($deletedConseil);
    }

    /**
     * TEST: Non-expert cannot access expert conseil routes
     */
    public function testNonExpertCannotAccessExpertConseilRoutes(): void
    {
        $this->logout();
        $this->loginAsUser();

        self::$client->request('GET', '/expert/conseils');

        $this->assertResponseRedirects('/dashboard');
    }

    /**
     * Helper: Create an analyse for testing
     */
    private function createAnalyse(): Analyse
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
        $analyse->setFerme($ferme);
        $analyse->setDemandeur($user);
        $analyse->setTechnicien($this->expert); // Set the logged-in expert as technicien

        self::$em->persist($ferme);
        self::$em->persist($analyse);
        self::$em->flush();

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
        $conseil->setPriorite(Priorite::HAUTE);
        $conseil->setAnalyse($analyse);

        self::$em->persist($conseil);
        self::$em->flush();

        return $conseil;
    }
}
