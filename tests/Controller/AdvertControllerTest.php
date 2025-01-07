<?php

namespace App\Tests\Controller;

use App\Entity\Adverts;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;

class AdvertControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateAdvert(): void
    {
        // Simulate a logged-in user
        $user = $this->createMockUser();
        $this->client->loginUser($user);
        $this->createMockCategory();

        // Go to the advert creation page
        $crawler = $this->client->request('GET', '/advert/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create a New Advert');

        // Submit the form with valid data
        $form = $crawler->selectButton('Create Advert')->form([
            'advert_create_form[title]' => 'Test Advert',
            'advert_create_form[description]' => 'This is a test advert.',
            'advert_create_form[price]' => '100.00',
            'advert_create_form[location]' => 'Test Location',
            'advert_create_form[category]' => 1, // id of first category made
        ]);
        $this->client->submit($form);

        // Assert that the advert was created successfully
        $this->assertResponseRedirects('/adverts');
        $this->client->followRedirect();

        // Verify the advert exists in the database
        $advertRepository = static::getContainer()->get(AdvertRepository::class);
        $advert = $advertRepository->findOneBy(['title' => 'Test Advert']);
        $this->assertNotNull($advert, 'Advert was not found in the database.');
        $this->assertEquals('This is a test advert.', $advert->getDescription());
    }

    public function testDeleteAdvert(): void
    {
        // Step 1: Simulate a logged-in user with ROLE_MODERATOR
        $user = $this->createMockUser(["ROLE_MODERATOR"]);
        $this->client->loginUser($user);  // Automatically manages session and authentication

        // Ensure session is initialized with a request (this triggers kernel.request)
        $this->client->request('GET', '/');  // This ensures the session is set up properly

        // Check if the session is recognized
        $securityContext = static::getContainer()->get('security.token_storage');
        dump($securityContext->getToken()->getUser());  // Should show the logged-in user

        // Mock an advert to delete
        $advert = $this->createMockAdvert($user);

        // Generate CSRF token (now it should have access to the session)
        $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
        $csrfToken = $csrfTokenManager->getToken('delete' . $advert->getId());

        // Ensure session cookie is being sent with the request
        $cookies = $this->client->getCookieJar()->all();
        dump($cookies);  // Check if the session cookie is present

        // Step 2: Submit the delete request with CSRF token
        $this->client->request('POST', '/advert/delete/' . $advert->getId(), [
            '_token' => $csrfToken->getValue(),
        ]);

        // Step 3: Assert redirection and verify deletion
        $this->assertResponseRedirects('/adverts');  // Check that the redirect happens
        $this->client->followRedirect();  // Follow the redirect

        // Step 4: Verify the advert has been deleted
        $advertRepository = static::getContainer()->get(AdvertRepository::class);
        $this->assertNull($advertRepository->find($advert->getId()), 'Advert was not deleted.');
    }



    private function createMockUser(array $roles = ["ROLE_USER"]): User
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create a unique email
        $uniqueEmail = sprintf('testuser%d@example.com', random_int(1, 10000));

        // Create a mock user with a unique email
        $user = new \App\Entity\User();
        $user->setEmail($uniqueEmail);
        $user->setName('Test User');
        $user->setPassword(
            static::getContainer()->get('security.password_hasher')
                ->hashPassword($user, 'password123')
        );
        $user->setRoles($roles);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createMockAdvert(User $user): Adverts
    {
        $advert = new Adverts();
        $advert->setTitle('Mock Advert');
        $advert->setDescription('Mock description');
        $advert->setPrice('50.00');
        $advert->setLocation('Mock Location');
        $advert->setCategory($this->createMockCategory());
        $advert->setUser($user);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($advert);
        $entityManager->flush();

        return $advert;
    }

    private function createMockCategory():Category
    {
        $category = new Category();
        $category->setName('Mock Category');
        $category->setDescription('Mock description');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }
}
