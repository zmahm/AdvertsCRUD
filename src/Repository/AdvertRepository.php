<?php

namespace App\Repository;

use App\Entity\Adverts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adverts::class);
    }

    /**
     * Fetch paginated adverts with optional filters
     */
    public function getPaginatedAdverts(int $page, int $limit = 10, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('advert') // Changed alias from 'a' to 'advert'
        ->orderBy('advert.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        // Apply filters
        if (!empty($filters['category'])) {
            $qb->andWhere('advert.category = :category')
                ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['minPrice'])) {
            $qb->andWhere('advert.price >= :minPrice')
                ->setParameter('minPrice', $filters['minPrice']);
        }

        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('advert.price <= :maxPrice')
                ->setParameter('maxPrice', $filters['maxPrice']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('advert.location LIKE :location')
                ->setParameter('location', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['user'])) {
            $qb->andWhere('advert.user = :user')
                ->setParameter('user', $filters['user']);
        }

        return new Paginator($qb->getQuery(), true);
    }

}
