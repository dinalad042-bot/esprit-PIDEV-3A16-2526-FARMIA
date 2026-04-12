<?php

namespace App\Tests\Functional;

use App\Tests\BaseWebTestCase;

/**
 * Template Rendering Tests.
 *
 * TEST: Verify all key pages load and render correctly
 * Reason: Template errors cause 500 errors that break user experience
 * Fat tail covered: Missing variables, template syntax errors, partial includes
 *
 * @covers \App\Controller templates
 */
class TemplateRenderingTest extends BaseWebTestCase
{
    /**
     * TEST: Homepage loads successfully
     */
    public function testHomepageLoads(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * TEST: Login page renders
     */
    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * TEST: Ferme index page renders with data
     */
    public function testFermeIndexPageRenders(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->client->request('GET', '/ferme/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * TEST: Animal index page renders
     */
    public function testAnimalIndexPageRenders(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->client->request('GET', '/animal/');
        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Plante index page renders
     */
    public function testPlanteIndexPageRenders(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->client->request('GET', '/plante/');
        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Analyse index page renders
     */
    public function testAnalyseIndexPageRenders(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $this->client->request('GET', '/analyse/');
        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Conseil index page renders
     */
    public function testConseilIndexPageRenders(): void
    {
        $this->loginWithRole('ROLE_EXPERT');
        $this->client->request('GET', '/conseil/');
        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Admin dashboard renders (if accessible)
     */
    public function testAdminDashboardRenders(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->client->request('GET', '/admin/dashboard');
        // May redirect if not configured, just check it doesn't 500
        $this->assertResponseStatusCodeIsOneOf([200, 301, 302, 403]);
    }

    /**
     * TEST: Base layout renders correctly
     */
    public function testBaseLayoutRenders(): void
    {
        $this->loginWithRole('ROLE_USER');
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        // Check that basic HTML structure exists
        $this->assertSelectorExists('html');
        $this->assertSelectorExists('body');
    }
}
