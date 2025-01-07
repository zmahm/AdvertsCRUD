<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_GB');

        // Load passwords from environment variables
        $adminPassword = $_ENV['ADMIN_FIXTURES_PASSWORD'];
        $moderatorPassword = $_ENV['MODERATOR_FIXTURES_PASSWORD'];
        $userPassword = $_ENV['USER_FIXTURES_PASSWORD'];

        // Create Admin User
        $adminUser = new User();
        $adminUser->setName('Admin User')
            ->setEmail('admin@test.com')
            ->setRoles(['ROLE_ADMIN']);
        $hashedAdminPassword = $this->passwordHasher->hashPassword($adminUser, $adminPassword);
        $adminUser->setPassword($hashedAdminPassword);
        $manager->persist($adminUser);

        // Reference for other fixtures
        $this->addReference('user_admin', $adminUser);

        // Create Moderator User
        $moderatorUser = new User();
        $moderatorUser->setName('Moderator User')
            ->setEmail('moderator@test.com')
            ->setRoles(['ROLE_MODERATOR']);
        $hashedModeratorPassword = $this->passwordHasher->hashPassword($moderatorUser, $moderatorPassword);
        $moderatorUser->setPassword($hashedModeratorPassword);
        $manager->persist($moderatorUser);

        // Reference for other fixtures
        $this->addReference('user_moderator', $moderatorUser);

        // Create Regular Users
        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            $user->setName($faker->name)
                ->setEmail($faker->unique()->safeEmail)
                ->setRoles(['ROLE_USER']);
            $hashedUserPassword = $this->passwordHasher->hashPassword($user, $userPassword);
            $user->setPassword($hashedUserPassword);
            $manager->persist($user);

            // Reference for other fixtures
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }
}
