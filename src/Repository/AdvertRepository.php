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
     * Fetch paginated adverts with optional filters.
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return Paginator
     */
    public function getPaginatedAdverts(int $page, int $limit = 10, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            // TODO: probably should add datetime for this for another filter option :(
            ->orderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        // Apply filters
        if (!empty($filters['category'])) {
            $qb->andWhere('a.category = :category')
                ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['minPrice'])) {
            $qb->andWhere('a.price >= :minPrice')
                ->setParameter('minPrice', $filters['minPrice']);
        }

        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('a.price <= :maxPrice')
                ->setParameter('maxPrice', $filters['maxPrice']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('a.location LIKE :location')
                ->setParameter('location', '%' . $filters['location'] . '%');
        }

        return new Paginator($qb->getQuery(), true);
    }
}
