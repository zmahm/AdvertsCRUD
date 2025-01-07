<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for the RegistrationController.
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Tests the registration process.
     */
    public function testRegistration(): void
    {
        // Create a client for the test
        $client = static::createClient();

        // Navigate to the registration page
        $crawler = $client->request('GET', '/register');

        // Ensure the registration page loads correctly
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');

        // Find the form and populate it with valid data
        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'test@example.com',
            'registration_form[name]' => 'Test User',
            'registration_form[plainPassword][first]' => 'password123',
            'registration_form[plainPassword][second]' => 'password123',
        ]);

        // Submit the form
        $client->submit($form);

        // Assert the response redirects to the login page
        $this->assertResponseRedirects('/login');

        // Follow the redirect and check for a success flash message
        $crawler = $client->followRedirect();

        // Additional assertions: Ensure the user was created in the database
        $container = static::getContainer();
        $user = $container->get('doctrine')->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user, 'User was not created in the database.');
        $this->assertEquals('test@example.com', $user->getEmail());
    }
}
