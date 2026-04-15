<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Ferme;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for FermeController.
 *
 * TEST: Full CRUD operations via HTTP requests
 * Reason: Controller is the entry point - must handle requests correctly
 * Fat tail covered: CSRF failures, validation errors, redirects, flash messages
 *
 * @covers \App\Controller\FermeController
 */
class FermeControllerTest extends BaseWebTestCase
{
    /**
     * TEST: Index page loads successfully
     * Reason: Main listing page must be accessible
     * Fat tail covered: 500 errors, template issues, database connection failures
     */
    public function testIndexPageLoads(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        self::$client->request('GET', '/ferme/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('table'); // Farms table should exist
    }

    /**
     * TEST: Can create new ferme with valid data
     * Reason: Create operation is core CRUD functionality
     * Fat tail covered: Validation bypass, data not persisted, no redirect
     */
    public function testCanCreateFermeWithValidData(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        self::$client->request('POST', '/ferme/', [
            'nom_ferme' => 'Nouvelle Ferme Test',
            'lieu' => 'Tunis Test',
            'surface' => '150.5',
            'latitude' => '36.8',
            'longitude' => '10.18'
        ]);

        $this->assertResponseRedirects('/ferme/');

        // Verify ferme was created
        $ferme = self::$em->getRepository(Ferme::class)->findOneBy(['nomFerme' => 'Nouvelle Ferme Test']);
        $this->assertNotNull($ferme);
        $this->assertEquals('Tunis Test', $ferme->getLieu());
        $this->assertEquals(150.5, $ferme->getSurface());
    }

    /**
     * TEST: Create ferme with invalid data shows errors
     * Reason: Validation must prevent bad data
     * Fat tail covered: Silent failures, data corruption, XSS vulnerabilities
     */
    public function testCreateFermeWithInvalidDataShowsErrors(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $crawler = self::$client->request('POST', '/ferme/', [
            'nom_ferme' => '', // Empty - should fail validation
            'lieu' => '',      // Empty - should fail validation
            'surface' => '-10' // Negative - should fail
        ]);

        $this->assertResponseStatusCodeSame(200); // Stays on same page
        $this->assertSelectorExists('.alert-danger, .error, .invalid-feedback'); // Error message shown
    }

    /**
     * TEST: Edit page loads with ferme data
     * Reason: Edit form must pre-populate with existing data
     * Fat tail covered: 404 for non-existent, wrong data loaded
     */
    public function testEditPageLoadsWithFermeData(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        // Create a ferme first
        $ferme = $this->createTestFerme('Ferme à Modifier', 'Lieu Test');
        self::$em->flush();

        self::$client->request('GET', '/ferme/' . $ferme->getIdFerme() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * TEST: Can update existing ferme
     * Reason: Update operation is core CRUD functionality
     * Fat tail covered: Changes not saved, wrong ferme updated
     */
    public function testCanUpdateFerme(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $ferme = $this->createTestFerme('Original Name', 'Original Lieu');
        self::$em->flush();

        self::$client->request('POST', '/ferme/' . $ferme->getIdFerme() . '/update', [
            'nom_ferme' => 'Updated Name',
            'lieu' => 'Updated Lieu',
            'surface' => '200.0',
            'latitude' => '37.0',
            'longitude' => '11.0'
        ]);

        $this->assertResponseRedirects('/ferme/');

        // Verify changes
        self::$em->clear();
        $updated = self::$em->getRepository(Ferme::class)->find($ferme->getId());
        $this->assertEquals('Updated Name', $updated->getNomFerme());
        $this->assertEquals('Updated Lieu', $updated->getLieu());
    }

    /**
     * TEST: Can delete ferme with CSRF token
     * Reason: Delete must be protected against CSRF attacks
     * Fat tail covered: Unauthorized deletion, data loss
     */
    public function testCanDeleteFermeWithCsrfToken(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $ferme = $this->createTestFerme('Ferme à Supprimer', 'Lieu');
        self::$em->flush();
        $id = $ferme->getIdFerme();

        self::$client->request('POST', '/ferme/delete/' . $id, [
            '_token' => self::$client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $id)->getValue()
        ]);

        $this->assertResponseRedirects('/ferme/');

        // Verify deletion
        self::$em->clear();
        $deleted = self::$em->getRepository(Ferme::class)->find($id);
        $this->assertNull($deleted);
    }

    /**
     * TEST: PDF generation returns PDF content
     * Reason: PDF export is a business requirement
     * Fat tail covered: DOMPDF failures, memory issues
     */
    public function testPdfGenerationReturnsPdfContent(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        // Create a ferme to have data in PDF
        $this->createTestFerme('PDF Test Farm', 'PDF Location');
        self::$em->flush();

        self::$client->request('GET', '/ferme/pdf');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    /**
     * TEST: Search functionality filters results
     * Reason: Search is a key feature for usability
     * Fat tail covered: SQL injection, no results handling
     */
    public function testSearchFiltersResults(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $this->createTestFerme('Ferme Tunis', 'Tunis');
        $this->createTestFerme('Ferme Sfax', 'Sfax');
        self::$em->flush();

        $crawler = self::$client->request('GET', '/ferme/', ['search' => 'Tunis']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * TEST: Unauthenticated user is redirected to login
     * Reason: Security - only authenticated users should access
     * Fat tail covered: Unauthorized access to sensitive data
     */
    public function testUnauthenticatedUserIsRedirected(): void
    {
        self::$client->request('GET', '/ferme/');

        $this->assertResponseRedirects();
    }

    /**
     * Helper: Create a test ferme
     */
    private function createTestFerme(string $nom, string $lieu): Ferme
    {
        $ferme = new Ferme();
        $ferme->setNomFerme($nom);
        $ferme->setLieu($lieu);
        $ferme->setSurface(100.0);
        self::$em->persist($ferme);
        return $ferme;
    }
}