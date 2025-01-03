<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Fetch paginated users with optional filters.
     */
    public function getPaginatedUsers(int $page, int $limit = 10, array $filters = []): Paginator
    {
        // Create a query builder instance
        $qb = $this->createQueryBuilder('user')
            ->orderBy('user.id', 'DESC') // Order by user ID DESC TODO: Add creation_date for user
            ->setFirstResult(($page - 1) * $limit) // Pagination offset
            ->setMaxResults($limit); // Pagination limit

        // Apply filters
        if (!empty($filters['email'])) {
            $qb->andWhere('user.email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('user.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        // Apply role filter
        if (!empty($filters['role'])) {
            $qb->andWhere('user.roles LIKE :role')
                ->setParameter('role', '%"' . $filters['role'] . '"%');
        }


        return new Paginator($qb->getQuery(), true);
    }
}
