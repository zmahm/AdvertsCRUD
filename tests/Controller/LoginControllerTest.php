<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Functional tests for the LoginController.
 */
class LoginControllerTest extends WebTestCase
{
    /**
     * Tests the login page accessibility and functionality.
     */
    public function testLogin(): void
    {
        $client = static::createClient();

        // Create a mock user in the database
        $user = $this->createMockUser();

        // Go to the login page
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');

        // Fill and submit the login form with the mock user's credentials
        $form = $crawler->selectButton('Login')->form([
            'email' => $user->getEmail(),
            'password' => 'password123', // Use the plain password set in createMockUser
        ]);
        $client->submit($form);

        // Follow the redirect and verify successful login
        $client->followRedirect();

        // Assert that the user is authenticated
        $tokenStorage = $client->getContainer()->get(TokenStorageInterface::class);
        $token = $tokenStorage->getToken();
        $this->assertNotNull($token, 'No security token found.');
        $this->assertEquals($user->getEmail(), $token->getUser()->getUserIdentifier());
    }

    /**
     * Creates a mock user and persists it to the test database.
     */
    private function createMockUser(): User
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create a unique email to avoid conflicts with other tests
        $uniqueEmail = sprintf('testuser%d@example.com', random_int(1, 10000));

        // Create the user entity
        $user = new User();
        $user->setEmail($uniqueEmail);
        $user->setName('Test User');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            static::getContainer()
                ->get('security.password_hasher')
                ->hashPassword($user, 'password123') // Set the hashed password
        );

        // Persist the user
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
