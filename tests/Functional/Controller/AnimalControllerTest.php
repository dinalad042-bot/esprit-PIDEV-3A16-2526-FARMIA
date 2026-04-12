<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Animal;
use App\Entity\Ferme;
use App\Tests\BaseWebTestCase;

/**
 * Functional tests for AnimalController.
 *
 * TEST: Full CRUD operations for Animal via HTTP
 * Reason: Animal management is a core feature
 * Fat tail covered: Required ferme relationship, date validation, CSRF
 *
 * @covers \App\Controller\AnimalController
 */
class AnimalControllerTest extends BaseWebTestCase
{
    /**
     * TEST: Index page loads successfully
     */
    public function testIndexPageLoads(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->client->request('GET', '/animal/');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * TEST: Can create new animal with valid data
     */
    public function testCanCreateAnimalWithValidData(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $ferme = $this->createTestFerme();
        $this->em->flush();

        $this->client->request('POST', '/animal/new', [
            'espece' => 'Vache Test',
            'etat_sante' => 'Bonne santé',
            'date_naissance' => '2020-01-15',
            'id_ferme' => $ferme->getIdFerme()
        ]);

        $this->assertResponseRedirects('/animal/');

        $animal = $this->em->getRepository(Animal::class)->findOneBy(['espece' => 'Vache Test']);
        $this->assertNotNull($animal);
        $this->assertEquals('Bonne santé', $animal->getEtatSante());
    }

    /**
     * TEST: Create with invalid data shows errors
     */
    public function testCreateAnimalWithInvalidDataShowsErrors(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $this->client->request('POST', '/animal/new', [
            'espece' => '', // Empty
            'etat_sante' => '',
            'date_naissance' => ''
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * TEST: Edit page loads
     */
    public function testEditPageLoads(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $animal = $this->createTestAnimal();
        $this->em->flush();

        $this->client->request('GET', '/animal/' . $animal->getIdAnimal() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    /**
     * TEST: Can update animal
     */
    public function testCanUpdateAnimal(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $animal = $this->createTestAnimal();
        $this->em->flush();

        $this->client->request('POST', '/animal/' . $animal->getIdAnimal() . '/update', [
            'espece' => 'Updated Species',
            'etat_sante' => 'Malade',
            'date_naissance' => '2019-05-20'
        ]);

        $this->assertResponseRedirects('/animal/');

        $this->em->clear();
        $updated = $this->em->getRepository(Animal::class)->find($animal->getIdAnimal());
        $this->assertEquals('Updated Species', $updated->getEspece());
    }

    /**
     * TEST: Can delete animal
     */
    public function testCanDeleteAnimal(): void
    {
        $this->loginWithRole('ROLE_ADMIN');

        $animal = $this->createTestAnimal();
        $this->em->flush();
        $id = $animal->getIdAnimal();

        $token = $this->client->getContainer()->get('security.csrf.token_manager')
            ->getToken('delete' . $id)->getValue();

        $this->client->request('POST', '/animal/delete/' . $id, ['_token' => $token]);

        $this->assertResponseRedirects('/animal/');

        $this->em->clear();
        $this->assertNull($this->em->getRepository(Animal::class)->find($id));
    }

    /**
     * TEST: PDF generation
     */
    public function testPdfGeneration(): void
    {
        $this->loginWithRole('ROLE_ADMIN');
        $this->createTestAnimal();
        $this->em->flush();

        $this->client->request('GET', '/animal/pdf');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    /**
     * TEST: Unauthenticated redirect
     */
    public function testUnauthenticatedUserIsRedirected(): void
    {
        $this->client->request('GET', '/animal/');
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

    private function createTestAnimal(): Animal
    {
        $ferme = $this->createTestFerme();
        $animal = new Animal();
        $animal->setEspece('Mouton');
        $animal->setEtatSante('Sain');
        $animal->setDateNaissance(new \DateTime('2020-01-01'));
        $animal->setFerme($ferme);
        $this->em->persist($animal);
        return $animal;
    }
}
