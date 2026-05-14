<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\UserLog;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the UserLog entity (Audit Logging).
 *
 * @covers \App\Entity\UserLog
 */
class UserLogTest extends TestCase
{
    public function testActionType(): void
    {
        $log = new UserLog();

        $log->setActionType('LOGIN');
        $this->assertEquals('LOGIN', $log->getActionType());

        $log->setActionType(null);
        $this->assertNull($log->getActionType());
    }

    public function testTimestamp(): void
    {
        $log = new UserLog();

        $now = new \DateTime();
        $log->setTimestamp($now);

        $this->assertSame($now, $log->getTimestamp());
        $this->assertInstanceOf(\DateTimeInterface::class, $log->getTimestamp());
    }

    public function testAffectedUser(): void
    {
        $log = new UserLog();
        $user = new User();

        $log->setUser($user);
        $this->assertSame($user, $log->getUser());

        $log->setUser(null);
        $this->assertNull($log->getUser());
    }

    public function testPerformedByUser(): void
    {
        $log = new UserLog();
        $admin = new User();
        $target = new User();

        $log->setPerformedBy($admin);
        $log->setUser($target);

        $this->assertSame($admin, $log->getPerformedBy());
        $this->assertSame($target, $log->getUser());
    }

    public function testDescription(): void
    {
        $log = new UserLog();

        $log->setDescription('User logged in successfully');
        $this->assertEquals('User logged in successfully', $log->getDescription());

        $log->setDescription(null);
        $this->assertNull($log->getDescription());
    }

    public function testFluentInterface(): void
    {
        $log = new UserLog();

        $this->assertSame($log, $log->setActionType('TEST'));
        $this->assertSame($log, $log->setDescription('Test description'));
        $this->assertSame($log, $log->setTimestamp(new \DateTime()));
    }

    public function testLegacyAliases(): void
    {
        $log = new UserLog();

        // Test action alias
        $log->setAction('CREATE');
        $this->assertEquals('CREATE', $log->getAction());
        $this->assertEquals('CREATE', $log->getActionType());

        // Test date alias for timestamp
        $date = new \DateTime('2024-01-15');
        $log->setDate($date);
        $this->assertSame($date, $log->getDate());
        $this->assertSame($date, $log->getTimestamp());

        // Test statut alias for description
        $log->setStatut('Success');
        $this->assertEquals('Success', $log->getStatut());
        $this->assertEquals('Success', $log->getDescription());
    }
}
