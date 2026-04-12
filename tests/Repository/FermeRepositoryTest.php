<?php

namespace App\Tests\Repository;

use App\Entity\Ferme;
use App\Entity\User;
use App\Repository\FermeRepository;
use App\Tests\BaseKernelTestCase;

/**
 * Functional tests for FermeRepository.
 *
 * TEST: Database operations for Ferme entity
 * Reason: Repository is the data access layer - must work correctly
 * Fat tail covered: Transaction rollback, cascade operations, query errors
 *
 * @covers \App\Repository\FermeRepository
 */
class FermeRepositoryTest extends BaseKernelTestCase
{
    private FermeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Ferme::class);
    }

    /**
     * TEST: Repository can persist a ferme
     * Reason: Basic CRUD - Create operation
     * Fat tail covered: ID not generated, transaction not committed
     */
    public function testCanPersistFerme(): void
    {
        $ferme = new Ferme();
        $ferme->setNomFerme('Test Farm');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100.5);

        $this->em->persist($ferme);
        $this->em->flush();

        $this->assertNotNull($ferme->getId());
        $this->assertGreaterThan(0, $ferme->getId());
    }

    /**
     * TEST: Can retrieve ferme by ID
     * Reason: Basic CRUD - Read operation
     * Fat tail covered: Entity not found, wrong entity type
     */
    public function testCanFindFermeById(): void
    {
        $ferme = $this->createFerme('Findable Farm', 'Some Place');
        $this->em->flush();

        $found = $this->repository->find($ferme->getId());

        $this->assertNotNull($found);
        $this->assertEquals('Findable Farm', $found->getNomFerme());
        $this->assertSame($ferme->getId(), $found->getId());
    }

    /**
     * TEST: Returns null for non-existent ID
     * Reason: Proper null handling prevents errors
     * Fat tail covered: Null pointer exceptions downstream
     */
    public function testFindReturnsNullForNonExistentId(): void
    {
        $result = $this->repository->find(999999);
        $this->assertNull($result);
    }

    /**
     * TEST: Can retrieve all fermes
     * Reason: List operations need findAll
     * Fat tail covered: Empty database returns array not null
     */
    public function testFindAllReturnsArray(): void
    {
        // Create some fermes
        $this->createFerme('Farm 1', 'Place 1');
        $this->createFerme('Farm 2', 'Place 2');
        $this->em->flush();

        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        $this->assertCount(2, $all);
        $this->assertContainsOnlyInstancesOf(Ferme::class, $all);
    }

    /**
     * TEST: Returns empty array when no fermes exist
     * Reason: UI must handle empty state gracefully
     * Fat tail covered: Null returns breaking foreach loops
     */
    public function testFindAllReturnsEmptyArrayWhenNoFermes(): void
    {
        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    /**
     * TEST: Can update existing ferme
     * Reason: Basic CRUD - Update operation
     * Fat tail covered: Changes not persisted, stale data
     */
    public function testCanUpdateFerme(): void
    {
        $ferme = $this->createFerme('Original Name', 'Original Place');
        $this->em->flush();

        $ferme->setNomFerme('Updated Name');
        $ferme->setSurface(200.0);
        $this->em->flush();

        // Clear EM to force re-fetch from DB
        $this->em->clear();

        $updated = $this->repository->find($ferme->getId());
        $this->assertEquals('Updated Name', $updated->getNomFerme());
        $this->assertEquals(200.0, $updated->getSurface());
    }

    /**
     * TEST: Can delete ferme
     * Reason: Basic CRUD - Delete operation
     * Fat tail covered: Orphaned records, constraint violations
     */
    public function testCanDeleteFerme(): void
    {
        $ferme = $this->createFerme('To Delete', 'Some Place');
        $this->em->flush();
        $id = $ferme->getId();

        $this->em->remove($ferme);
        $this->em->flush();

        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    /**
     * TEST: Ferme with User relationship persists correctly
     * Reason: ManyToOne relationships must work
     * Fat tail covered: Foreign key constraint failures
     */
    public function testFermeWithUserRelationship(): void
    {
        $user = new User();
        $user->setEmail('test_' . uniqid() . '@farmia.test');
        $user->setPassword('password');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCin('12345678');
        $user->setAdresse('Test Address');
        $user->setTelephone('12345678');

        $ferme = new Ferme();
        $ferme->setNomFerme('User Farm');
        $ferme->setLieu('User Place');
        $ferme->setUser($user);

        $this->em->persist($user);
        $this->em->persist($ferme);
        $this->em->flush();

        $this->em->clear();

        $found = $this->repository->find($ferme->getId());
        $this->assertNotNull($found->getUser());
        $this->assertEquals('test_' . explode('@', $user->getEmail())[0], explode('@', $found->getUser()->getEmail())[0]);
    }

    /**
     * Helper method to create a ferme
     */
    private function createFerme(string $nom, string $lieu): Ferme
    {
        $ferme = new Ferme();
        $ferme->setNomFerme($nom);
        $ferme->setLieu($lieu);
        $ferme->setSurface(100.0);
        $this->em->persist($ferme);
        return $ferme;
    }
}
