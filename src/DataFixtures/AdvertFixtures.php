<?php

namespace App\DataFixtures;

use App\Entity\Adverts;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class AdvertFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_GB');

        // Load users from AppFixtures
        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $users[] = $this->getReference('user_' . $i, User::class);
        }

        // Load categories from CategoryFixtures
        $categories = [];
        for ($i = 1; $i <= 5; $i++) {
            $categories[] = $this->getReference('category_' . $i, Category::class);
        }

        // Create 20 adverts (1 for each base user)
        foreach ($users as $user) {
            $advert = new Adverts();
            $advert->setTitle($faker->sentence(10))
                ->setDescription($faker->sentence(20))
                ->setPrice($faker->randomFloat(2, 10, 1000)) // Random price between 10 and 1000
                ->setLocation($faker->city)
                ->setUser($user)
                ->setCategory($categories[array_rand($categories)]);

            $manager->persist($advert);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,      // Load users first
            CategoryFixtures::class, // Load categories first
        ];
    }
}
