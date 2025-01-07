<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Functional tests for the UserManagementController.
 */
class UserManagementControllerTest extends WebTestCase
{
    /**
     * Tests the user management page functionality.
     */
    public function testUserManagementPage(): void
    {
        // Arrange
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        // Act
        $crawler = $client->request('GET', '/admin/users');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'User Management');
        $this->assertSelectorExists('table'); // Check for user table
        $this->assertSelectorExists('form'); // Check for filter form
    }

    /**
     * Tests updating a user's role.
     */
    public function testUpdateUserRole(): void
    {
        // Arrange
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        // Create a test user
        $testUser = $this->createTestUser();
        $entityManager = $this->getEntityManager();
        $entityManager->persist($testUser);
        $entityManager->flush();

        // Act: Send a POST request to update the user's role
        $client->request('POST', '/admin/users/' . $testUser->getId() . '/update-role', [
            'role' => 'ROLE_MODERATOR',
        ]);

        // Assert: Ensure the role update was successful
        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'User role updated successfully.');

        // Verify the role was updated in the database
        $updatedUser = $entityManager->getRepository(User::class)->find($testUser->getId());
        $this->assertEquals(['ROLE_MODERATOR'], $updatedUser->getRoles());
    }

    /**
     * Tests attempting to assign an invalid role.
     */
    public function testInvalidRoleUpdate(): void
    {
        // Arrange
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        // Create a test user
        $testUser = $this->createTestUser();
        $entityManager = $this->getEntityManager();
        $entityManager->persist($testUser);
        $entityManager->flush();

        // Act: Send a POST request with an invalid role
        $client->request('POST', '/admin/users/' . $testUser->getId() . '/update-role', [
            'role' => 'INVALID_ROLE',
        ]);

        // Assert: Ensure the update was rejected
        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-danger', 'Invalid role selected.');

        // Verify the role remains unchanged
        $unchangedUser = $entityManager->getRepository(User::class)->find($testUser->getId());
        $this->assertEquals(['ROLE_USER'], $unchangedUser->getRoles());
    }

    /**
     * Helper method to create an admin user for testing.
     */
    private function createAdminUser(): User
    {
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com')
            ->setPassword('password') // Password hashing is not relevant for testing
            ->setRoles(['ROLE_ADMIN']);

        return $adminUser;
    }

    /**
     * Helper method to create a test user.
     */
    private function createTestUser(): User
    {
        $testUser = new User();
        $testUser->setEmail('user@example.com')
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        return $testUser;
    }

    /**
     * Helper method to retrieve the entity manager.
     */
    private function getEntityManager(): EntityManagerInterface
    {
        self::bootKernel();
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
