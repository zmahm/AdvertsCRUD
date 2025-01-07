<?php

namespace App\Tests\Entity;

use App\Entity\Adverts;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Adverts entity.
 */
class AdvertsTest extends TestCase
{
    /**
     * Tests getters and setters for the Adverts entity.
     */
    public function testAdvertEntity(): void
    {
        // Arrange
        $user = new User();
        $category = new Category();
        $advert = new Adverts();

        // Act
        $advert->setTitle('Test Advert')
            ->setDescription('A test description for the advert')
            ->setPrice('100.00')
            ->setLocation('London')
            ->setUser($user)
            ->setCategory($category);

        // Assert
        $this->assertEquals('Test Advert', $advert->getTitle());
        $this->assertEquals('A test description for the advert', $advert->getDescription());
        $this->assertEquals('100.00', $advert->getPrice());
        $this->assertEquals('London', $advert->getLocation());
        $this->assertSame($user, $advert->getUser());
        $this->assertSame($category, $advert->getCategory());
    }

    /**
     * Tests null values for user and category relationships.
     */
    public function testNullRelationships(): void
    {
        // Arrange
        $advert = new Adverts();

        // Act
        $advert->setUser(null)->setCategory(null);

        // Assert
        $this->assertNull($advert->getUser());
        $this->assertNull($advert->getCategory());
    }
}
