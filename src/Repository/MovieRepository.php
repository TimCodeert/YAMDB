<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function findOneWithDirector(int $id): ?Movie
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.director', 'd')
            ->addSelect('d')
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function createPaginationQuery(string $searchTerm = ''): Query
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.director', 'd')
            ->orderBy('m.id', 'ASC');

        if ($searchTerm) {
            $qb->andWhere('m.title LIKE :search OR d.name LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        return $qb->getQuery();
    }
}
