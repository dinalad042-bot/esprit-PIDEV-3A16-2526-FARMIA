<?php
namespace App\Tests\Unit\Entity;

use App\Entity\UserFace;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the UserFace entity.
 *
 * @covers \App\Entity\UserFace
 */
class UserFaceTest extends TestCase
{
    public function testUserFaceCreation(): void
    {
        $userFace = new UserFace();
        $this->assertInstanceOf(UserFace::class, $userFace);
    }
}
