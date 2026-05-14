<?php

namespace App\Tests\Repository;

use App\Entity\Animal;
use App\Entity\Ferme;
use App\Repository\AnimalRepository;
use App\Tests\BaseKernelTestCase;

/**
 * Functional tests for AnimalRepository.
 *
 * TEST: Database operations for Animal entity
 * Reason: Repository layer must correctly persist and retrieve animal data
 * Fat tail covered: Required ferme relationship, date handling, transaction safety
 *
 * @covers \App\Repository\AnimalRepository
 */
class AnimalRepositoryTest extends BaseKernelTestCase
{
    private AnimalRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Animal::class);
    }

    /**
     * TEST: Repository can persist an animal
     * Reason: Basic CRUD - Create operation
     * Fat tail covered: ID not generated, transaction rollback
     */
    public function testCanPersistAnimal(): void
    {
        $ferme = $this->createFerme();
        $animal = $this->createAnimal($ferme);

        $this->em->flush();

        $this->assertNotNull($animal->getIdAnimal());
        $this->assertGreaterThan(0, $animal->getIdAnimal());
    }

    /**
     * TEST: Can retrieve animal by ID
     * Reason: Basic CRUD - Read operation
     * Fat tail covered: Entity not found, type mismatch
     */
    public function testCanFindAnimalById(): void
    {
        $ferme = $this->createFerme();
        $animal = $this->createAnimal($ferme, 'Vache', 'Bonne santé');
        $this->em->flush();

        $found = $this->repository->find($animal->getIdAnimal());

        $this->assertNotNull($found);
        $this->assertEquals('Vache', $found->getEspece());
        $this->assertEquals('Bonne santé', $found->getEtatSante());
    }

    /**
     * TEST: Returns null for non-existent ID
     * Reason: Proper null handling prevents downstream errors
     * Fat tail covered: Null pointer exceptions in controllers
     */
    public function testFindReturnsNullForNonExistentId(): void
    {
        $result = $this->repository->find(999999);
        $this->assertNull($result);
    }

    /**
     * TEST: Can retrieve all animals
     * Reason: List operations need findAll
     * Fat tail covered: Empty result handling
     */
    public function testFindAllReturnsArray(): void
    {
        $ferme = $this->createFerme();
        $this->createAnimal($ferme, 'Mouton');
        $this->createAnimal($ferme, 'Chèvre');
        $this->em->flush();

        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        $this->assertCount(2, $all);
    }

    /**
     * TEST: Can update existing animal
     * Reason: Basic CRUD - Update operation
     * Fat tail covered: Changes not persisted
     */
    public function testCanUpdateAnimal(): void
    {
        $ferme = $this->createFerme();
        $animal = $this->createAnimal($ferme, 'Original');
        $this->em->flush();

        $animal->setEspece('Updated');
        $animal->setEtatSante('Malade');
        $this->em->flush();

        $this->em->clear();

        $updated = $this->repository->find($animal->getIdAnimal());
        $this->assertEquals('Updated', $updated->getEspece());
        $this->assertEquals('Malade', $updated->getEtatSante());
    }

    /**
     * TEST: Can delete animal
     * Reason: Basic CRUD - Delete operation
     * Fat tail covered: Constraint violations if animal referenced elsewhere
     */
    public function testCanDeleteAnimal(): void
    {
        $ferme = $this->createFerme();
        $animal = $this->createAnimal($ferme);
        $this->em->flush();
        $id = $animal->getIdAnimal();

        $this->em->remove($animal);
        $this->em->flush();

        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    /**
     * TEST: Animal with Ferme relationship persists correctly
     * Reason: Required ManyToOne relationship must work
     * Fat tail covered: Foreign key constraint failures
     */
    public function testAnimalWithFermeRelationship(): void
    {
        $ferme = $this->createFerme('Test Farm');
        $animal = $this->createAnimal($ferme);
        $this->em->flush();

        $this->em->clear();

        $found = $this->repository->find($animal->getIdAnimal());
        $this->assertNotNull($found->getFerme());
        $this->assertEquals('Test Farm', $found->getFerme()->getNomFerme());
    }

    /**
     * Helper: Create a ferme
     */
    private function createFerme(string $nom = 'Test Farm'): Ferme
    {
        $ferme = new Ferme();
        $ferme->setNomFerme($nom);
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100.0);
        $this->em->persist($ferme);
        return $ferme;
    }

    /**
     * Helper: Create an animal
     */
    private function createAnimal(Ferme $ferme, string $espece = 'Vache', string $etat = 'Sain'): Animal
    {
        $animal = new Animal();
        $animal->setEspece($espece);
        $animal->setEtatSante($etat);
        $animal->setDateNaissance(new \DateTime('2020-01-01'));
        $animal->setFerme($ferme);
        $this->em->persist($animal);
        return $animal;
    }
}
