<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the User entity.
 */
class UserTest extends TestCase
{
    /**
     * Tests the role functionality of the User entity.
     */
    public function testUserRoles(): void
    {
        // Arrange
        $user = new User();

        // Act
        $user->setRoles(['ROLE_USER']);

        // Assert
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * Tests setting and retrieving the email.
     */
    public function testEmail(): void
    {
        // Arrange
        $user = new User();

        // Act
        $user->setEmail('test@example.com');

        // Assert
        $this->assertEquals('test@example.com', $user->getEmail());
    }
}
