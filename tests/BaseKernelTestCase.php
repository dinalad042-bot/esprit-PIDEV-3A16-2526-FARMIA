<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Base class for repository and service tests.
 * Provides database isolation and entity manager access.
 */
abstract class BaseKernelTestCase extends KernelTestCase
{
    protected ?EntityManagerInterface $em = null;
    
    /**
     * Set up before each test.
     * Boots kernel and gets entity manager.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        self::bootKernel();
        
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        
        // Begin transaction for rollback after test
        $this->em->getConnection()->beginTransaction();
    }
    
    /**
     * Tear down after each test.
     * Rolls back transaction and clears entity manager.
     */
    protected function tearDown(): void
    {
        // Rollback transaction
        if ($this->em !== null && $this->em->getConnection()->isTransactionActive()) {
            $this->em->getConnection()->rollBack();
        }
        
        // Clear entity manager
        if ($this->em !== null) {
            $this->em->clear();
        }
        
        parent::tearDown();
    }
    
    /**
     * Get the entity manager.
     * 
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}