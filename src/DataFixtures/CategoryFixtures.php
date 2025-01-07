<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_REFERENCE = 'category';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_GB');

        for ($i = 1; $i <= 5; $i++) {
            $category = new Category();
            $category->setName($faker->word);
            $category->setDescription($faker->sentence);

            $manager->persist($category);

            // Add a reference for other fixtures to use
            $this->addReference('category_' . $i, $category);
        }

        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,      // Load users first
        ];
    }
}
