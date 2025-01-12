<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class UserManagementControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    private function mockCsrfTokenManager(): void
    {
        // Mock the CSRF token manager
        $csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);

        // Always return a specific token value for `getToken`
        $csrfTokenManager->method('getToken')
            ->willReturn(new CsrfToken('update_role123', 'valid_csrf_token_value'));

        // Always return true for `isTokenValid`
        $csrfTokenManager->method('isTokenValid')
            ->willReturn(true);

        // Replace the real service with the mocked one
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfTokenManager);
    }

    //basic test
    public function testUsersListAccess(): void
    {
        // Create an admin user and log in
        $admin = $this->createUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        // Add a test user for the user list
        $testUser = $this->createUser('user@example.com', ['ROLE_USER']);

        // Access the user management page
        $crawler = $this->client->request('GET', '/admin/users');
        $this->assertResponseIsSuccessful(); // Ensure the page loads successfully

        // Check that the users are visible
        $this->assertSelectorTextContains('table.user-management-table tbody', 'admin@example.com');
        $this->assertSelectorTextContains('table.user-management-table tbody', 'user@example.com');
    }


    //test for functioning role update of user
    public function testUpdateRoleSuccess(): void
    {
        // Mock the CSRF token manager for this specific test
        $this->mockCsrfTokenManager();

        // Create and log in as an admin user
        $admin = $this->createUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        // Create a test user to update
        $testUser = $this->createUser('user@example.com', ['ROLE_USER']);

        // Submit the role update request with the mocked CSRF token
        $this->client->request('POST', '/admin/users/update-role/' . $testUser->getId(), [
            '_token' => 'valid_csrf_token_value', // Matches the mocked token
            'role' => 'ROLE_MODERATOR',
        ]);

        // Assert successful redirection and role update
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();

        // Verify the user now has the updated role
        $updatedUser = $this->entityManager->getRepository(User::class)->find($testUser->getId());
        $this->assertContains('ROLE_MODERATOR', $updatedUser->getRoles());
    }

    //test for prevention of invalid role insertion
    public function testUpdateRoleInvalidRole(): void
    {
        // Mock the CSRF token manager for this specific test
        $this->mockCsrfTokenManager();

        // Create and log in as an admin user
        $admin = $this->createUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        // Create a test user
        $testUser = $this->createUser('user@example.com', ['ROLE_USER']);

        // Submit the form with an invalid role
        $this->client->request('POST', '/admin/users/update-role/' . $testUser->getId(), [
            '_token' => 'valid_csrf_token_value', // Matches the mocked token
            'role' => 'ROLE_INVALID',
        ]);

        // Assert the response redirects and displays an error
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();

        // Verify the user's role remains unchanged
        $unchangedUser = $this->entityManager->getRepository(User::class)->find($testUser->getId());
        $this->assertContains('ROLE_USER', $unchangedUser->getRoles());
    }

    /**
     * Helper method to create a user with specific roles
     */
    private function createUser(string $email, array $roles): User
    {
        $user = new User();
        $user->setEmail($email)
            ->setRoles($roles)
            ->setPassword(password_hash('password', PASSWORD_BCRYPT)) // Use a secure password
            ->setName('Test User');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
