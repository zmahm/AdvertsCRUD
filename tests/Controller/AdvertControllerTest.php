<?php

namespace App\Tests\Controller;

use App\Entity\Adverts;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class AdvertControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    //used as util when forms aren't used as that would usually deal with csrf for us
    private function mockCsrfTokenManager(): void
    {
        // Mock the CSRF token manager
        $csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);

        // Always return a specific token value for `getToken`
        $csrfTokenManager->method('getToken')
            ->willReturn(new CsrfToken('delete_advert123', 'valid_csrf_token_value'));

        // Always return true for `isTokenValid`
        $csrfTokenManager->method('isTokenValid')
            ->willReturn(true);

        // Replace the real service with the mocked one
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfTokenManager);
    }

    //test to create advert
    public function testCreateAdvert(): void
    {
        // Create a user and category, then log in as the user
        $user = $this->createUser('user@example.com', ['ROLE_USER']);
        $category = $this->createCategory('Test Category', 'Test Description');
        $this->client->loginUser($user);

        // Request the creation form page
        $crawler = $this->client->request('GET', '/advert/create');
        $this->assertResponseIsSuccessful();

        // Submit the creation form with valid data
        $form = $crawler->selectButton('Create Advert')->form([
            'advert_create_edit_form[title]' => 'Test Advert',
            'advert_create_edit_form[description]' => 'Test Description',
            'advert_create_edit_form[price]' => '100.00',
            'advert_create_edit_form[location]' => 'Test Location',
            'advert_create_edit_form[category]' => $category->getId(),
        ]);
        $this->client->submit($form);

        // Verify successful redirection and feedback message
        $this->assertResponseRedirects('/adverts');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Advert created successfully.');
    }

    //test to edit advert
    public function testEditAdvert(): void
    {
        // Create a user, category, and advert, then log in as the user
        $user = $this->createUser('user@example.com', ['ROLE_USER']);
        $category = $this->createCategory('Test Category', 'Test Description');
        $advert = $this->createAdvert($user, $category, 'Original Title', 'Original Description', '100.00', 'Original Location');
        $this->client->loginUser($user);

        // Request the edit form page
        $crawler = $this->client->request('GET', '/advert/edit/' . $advert->getId());
        $this->assertResponseIsSuccessful();

        // Submit the edit form with updated data
        $form = $crawler->selectButton('Update Advert')->form([
            'advert_create_edit_form[title]' => 'Updated Title',
            'advert_create_edit_form[description]' => 'Updated Description',
            'advert_create_edit_form[price]' => '150.00',
            'advert_create_edit_form[location]' => 'Updated Location',
            'advert_create_edit_form[category]' => $category->getId(),
        ]);
        $this->client->submit($form);

        // Verify successful redirection and feedback message
        $this->assertResponseRedirects('/adverts');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Advert updated successfully.');
    }

    //test to delete advert
    public function testDeleteAdvert(): void
    {
        // Mock the CSRF token manager for this specific test
        $this->mockCsrfTokenManager();

        // Create a moderator user, category, and advert, then log in as the moderator
        $user = $this->createUser('moderator@example.com', ['ROLE_MODERATOR']);
        $category = $this->createCategory('Test Category', 'Test Description');
        $advert = $this->createAdvert($user, $category, 'Title to Delete', 'Description to Delete', '200.00', 'Location to Delete');
        $this->client->loginUser($user);

        // Submit the delete request with the mocked CSRF token
        $this->client->request('POST', '/advert/delete/' . $advert->getId(), [
            '_token' => 'valid_csrf_token_value', // Matches the mocked token
        ]);

        // Verify successful redirection and feedback message
        $this->assertResponseRedirects('/adverts');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Advert deleted successfully.');
    }

    //tests to see if list of adverts show
    public function testListAdverts(): void
    {
        // Create a user and a category
        $user = $this->createUser('user@example.com', ['ROLE_USER']);
        $category = $this->createCategory('Electronics', 'All things electronic');

        // Create two adverts under the same user and category
        $advert1 = $this->createAdvert($user, $category, 'Advert 1', 'Description for Advert 1', '50.00', 'London');
        $advert2 = $this->createAdvert($user, $category, 'Advert 2', 'Description for Advert 2', '75.00', 'Manchester');

        // Request the adverts list page
        $crawler = $this->client->request('GET', '/adverts');
        $this->assertResponseIsSuccessful();

        // Ensure that two adverts are displayed
        $this->assertCount(2, $crawler->filter('.advert-card'));

        // Verify the details of the first advert
        $this->assertSelectorTextContains('#advertCard' . $advert1->getId() . ' .advert-title', 'Advert 1');
        $this->assertSelectorTextContains('#advertCard' . $advert1->getId() . ' .advert-price', '£50.00');

        // Verify the details of the second advert
        $this->assertSelectorTextContains('#advertCard' . $advert2->getId() . ' .advert-title', 'Advert 2');
        $this->assertSelectorTextContains('#advertCard' . $advert2->getId() . ' .advert-price', '£75.00');
    }

    //tests to see if single advert can show
    public function testViewAdvert(): void
    {
        $user = $this->createUser('user@example.com', ['ROLE_USER']);
        $category = $this->createCategory('Test Category', 'Test Description');
        $advert = $this->createAdvert($user, $category, 'View Test Title', 'View Test Description', '123.45', 'Test Location');

        // Test viewing an existing advert
        $this->client->request('GET', '/advert/view/' . $advert->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'View Test Title');

    }

    // Helper methods to create users, categories, and adverts for tests
    private function createUser(string $email, array $roles): User
    {
        $user = new User();
        $user->setEmail($email)
            ->setRoles($roles)
            ->setPassword(password_hash('password', PASSWORD_BCRYPT))
            ->setName('Test User');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createCategory(string $name, string $description): Category
    {
        $category = new Category();
        $category->setName($name)
            ->setDescription($description);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    private function createAdvert(User $user, Category $category, string $title, string $description, string $price, string $location): Adverts
    {
        $advert = new Adverts();
        $advert->setUser($user)
            ->setCategory($category)
            ->setTitle($title)
            ->setDescription($description)
            ->setPrice($price)
            ->setLocation($location);
        $this->entityManager->persist($advert);
        $this->entityManager->flush();

        return $advert;
    }



}
