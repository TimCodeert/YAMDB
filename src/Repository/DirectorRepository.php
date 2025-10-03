<?php

namespace App\Repository;

use App\Entity\Director;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Director>
 */
class DirectorRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Director::class);
        $this->em = $em;
    }

    public function upsertDirectorsByNames(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $names = array_unique($names);
        $serializedNames = array_map(fn($name) => $this->em->getConnection()->quote($name), $names);

        $values = implode(', ', array_map(fn($name) => "($name)", $serializedNames));
        $sql = "INSERT IGNORE INTO director (name) VALUES " . $values;

        $this->em->getConnection()->executeStatement($sql);

        $directors = $this->createQueryBuilder('d')
            ->where('d.name IN (:names)')
            ->setParameter('names', $names)
            ->getQuery()
            ->getResult();

        $lookupMap = [];
        foreach ($directors as $director) {
            $lookupMap[$director->getName()] = $director;
        }

        return $lookupMap;
    }

}
