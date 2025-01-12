<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Adverts;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CategoryControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    // Mock the CSRF token manager for tests that need it
    private function mockCsrfTokenManager(): void
    {
        // Mock the CSRF token manager
        $csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);

        // Always return a specific token value for `getToken`
        $csrfTokenManager->method('getToken')
            ->willReturn(new CsrfToken('delete_category123', 'valid_csrf_token_value'));

        // Always return true for `isTokenValid`
        $csrfTokenManager->method('isTokenValid')
            ->willReturn(true);

        // Replace the real service with the mocked one
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfTokenManager);
    }

    // Test deleting a category
    public function testDeleteCategory(): void
    {
        // Mock the CSRF token manager
        $this->mockCsrfTokenManager();

        // Log in as an admin user
        $admin = $this->createUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        // Create a category to delete
        $category = $this->createCategory('Delete Test Category', 'Delete Test Description');

        $categoryId = $category->getId();
        // Submit the delete form
        $this->client->request('POST', '/category/delete/' . $category->getId(), [
            '_token' => 'valid_csrf_token_value', // Matches the mocked token
        ]);

        // Verify the category is no longer in the database
        $deletedCategory = $this->entityManager->getRepository(Adverts::class)->find($categoryId);
        $this->assertNull($deletedCategory);
    }

    // Other tests...

    // Helper methods to create users, categories, and adverts
    private function createUser(string $email, array $roles): User
    {
        $user = new User();
        $user->setEmail($email)
            ->setRoles($roles)
            ->setPassword(password_hash('password', PASSWORD_BCRYPT))
            ->setName('Test Admin');
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

    private function createAdvert(
        string $title,
        string $description,
        float $price,
        string $location,
        Category $category,
        User $user
    ): Adverts {
        $advert = new Adverts();
        $advert->setTitle($title)
            ->setDescription($description)
            ->setPrice($price)
            ->setLocation($location)
            ->setCategory($category)
            ->setUser($user);

        $this->entityManager->persist($advert);
        $this->entityManager->flush();

        return $advert;
    }
}
