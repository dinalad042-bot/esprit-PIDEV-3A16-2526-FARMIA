<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Ferme;
use App\Entity\Plante;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for PlanteController.
 *
 * TEST: CRUD operations for Plante
 * Reason: Plant management is essential for farm operations
 * Fat tail covered: Quantity validation, ferme relationship
 *
 * @covers \App\Controller\PlanteController
 */
class PlanteControllerTest extends BaseWebTestCase
{
    public function testIndexPageLoads(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        self::$client->request('GET', '/plante/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testCanCreatePlante(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $ferme = $this->createTestFerme();
        self::$em->flush();

        self::$client->request('POST', '/plante/new', [
            'nom_espece' => 'Tomate Test',
            'cycle_vie' => 'Annuel',
            'quantite' => '50',
            'id_ferme' => $ferme->getIdFerme()
        ]);

        $this->assertResponseRedirects('/plante/');

        $plante = self::$em->getRepository(Plante::class)->findOneBy(['nomEspece' => 'Tomate Test']);
        $this->assertNotNull($plante);
        $this->assertEquals(50, $plante->getQuantite());
    }

    public function testCreateWithInvalidData(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        self::$client->request('POST', '/plante/new', [
            'nom_espece' => '',
            'cycle_vie' => '',
            'quantite' => '0'
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testEditPageLoads(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $plante = $this->createTestPlante();
        self::$em->flush();

        self::$client->request('GET', '/plante/' . $plante->getIdPlante() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testCanUpdatePlante(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $plante = $this->createTestPlante();
        self::$em->flush();

        self::$client->request('POST', '/plante/' . $plante->getIdPlante() . '/update', [
            'nom_espece' => 'Updated Plant',
            'cycle_vie' => 'Perenne',
            'quantite' => '100'
        ]);

        $this->assertResponseRedirects('/plante/');
    }

    public function testCanDeletePlante(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $plante = $this->createTestPlante();
        self::$em->flush();
        $id = $plante->getIdPlante();

        $token = self::$client->getContainer()->get('security.csrf.token_manager')
            ->getToken('delete' . $id)->getValue();

        self::$client->request('POST', '/plante/delete/' . $id, ['_token' => $token]);
        $this->assertResponseRedirects('/plante/');
    }

    public function testPdfGeneration(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->createTestPlante();
        self::$em->flush();

        self::$client->request('GET', '/plante/pdf');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testUnauthenticatedRedirect(): void
    {
        self::$client->request('GET', '/plante/');
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

    private function createTestPlante(): Plante
    {
        $ferme = $this->createTestFerme();
        $plante = new Plante();
        $plante->setNomEspece('Blé');
        $plante->setCycleVie('Annuel');
        $plante->setQuantite(100);
        $plante->setFerme($ferme);
        self::$em->persist($plante);
        return $plante;
    }
}
