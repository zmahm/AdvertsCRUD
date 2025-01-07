<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Category entity.
 */
class CategoryTest extends TestCase
{
    /**
     * Tests the getters and setters for the Category entity.
     */
    public function testCategoryEntity(): void
    {
        // Arrange
        $category = new Category();

        // Act
        $category->setName('Test Category');

        // Assert
        $this->assertEquals('Test Category', $category->getName());
    }
}
