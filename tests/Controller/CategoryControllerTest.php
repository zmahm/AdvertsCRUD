<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Adverts;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    // Test creating a category
    public function testCreateCategory(): void
    {
        // Log in as an admin user
        $admin = $this->createUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        // Request the create form page
        $crawler = $this->client->request('GET', '/category/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.text-purple', 'Create a New Advert Category For Users');

        // Submit the create form with valid data
        $form = $crawler->selectButton('Create Category')->form([
            'category_create_edit_form[name]' => 'Test Category',
            'category_create_edit_form[description]' => 'Test Description',
        ]);
        $this->client->submit($form);

        // Verify successful redirection and flash message
        $this->assertResponseRedirects('/categories');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Category created successfully.');

        // Verify the category was persisted
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => 'Test Category']);
        $this->assertNotNull($category);
        $this->assertEquals('Test Description', $category->getDescription());
    }


    // Test editing a category
    public function testEditCategory(): void
    {
        // Log in as an admin user
        $admin = $this->createUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($admin);

        // Create a category to edit
        $category = $this->createCategory('Original Name', 'Original Description');

        // Request the edit form page
        $crawler = $this->client->request('GET', '/category/edit/' . $category->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.text-purple', 'Edit Advert Category For Users');

        // Submit the edit form with updated data
        $form = $crawler->selectButton('Update Category')->form([
            'category_create_edit_form[name]' => 'Updated Name',
            'category_create_edit_form[description]' => 'Updated Description',
        ]);
        $this->client->submit($form);

        // Verify successful redirection and flash message
        $this->assertResponseRedirects('/categories');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Category updated successfully.');

        // Verify the category was updated
        $updatedCategory = $this->entityManager->getRepository(Category::class)->find($category->getId());
        $this->assertEquals('Updated Name', $updatedCategory->getName());
        $this->assertEquals('Updated Description', $updatedCategory->getDescription());
    }


    // Test listing categories
    public function testListCategories(): void
    {
        // Create some categories
        $this->createCategory('Category 1', 'Description 1');
        $this->createCategory('Category 2', 'Description 2');

        // Request the list page
        $crawler = $this->client->request('GET', '/categories');
        $this->assertResponseIsSuccessful();

        // Ensure the categories are displayed
        $this->assertCount(2, $crawler->filter('.advert-card'));

        // Verify the details of the first category
        $this->assertSelectorTextContains('.advert-card:nth-child(1) .advert-title', 'Category 1');
        $this->assertSelectorTextContains('.advert-card:nth-child(1) .advert-description', 'Description 1');

        // Verify the details of the second category
        $this->assertSelectorTextContains('.advert-card:nth-child(2) .advert-title', 'Category 2');
        $this->assertSelectorTextContains('.advert-card:nth-child(2) .advert-description', 'Description 2');
    }


    // Test viewing a single category with linked adverts
    public function testViewCategoryResults(): void
    {
        // Create a category and a user
        $category = $this->createCategory('View Test Category', 'View Test Description');
        $user = $this->createUser('user@example.com', ['ROLE_USER']);

        // Create an advert linked to the category and user
        $advert = $this->createAdvert(
            'Advert Title for Test',
            'Advert Description for Test',
            123.45,
            'Test Location',
            $category,
            $user
        );

        // Test viewing the categories list
        $crawler = $this->client->request('GET', '/categories');
        $this->assertResponseIsSuccessful();

        // Verify the category and advert details are displayed
        $this->assertSelectorTextContains('.advert-card .advert-title', $category->getName());

        // Verify the "View Adverts Under This Category" link works
        $link = $crawler->selectLink('View Adverts Under This Category')->link();
        $advertCrawler = $this->client->click($link);

        // Ensure the linked advert is displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.advert-title', 'Advert Title for Test');
    }



    // Helper methods to create users and categories
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
