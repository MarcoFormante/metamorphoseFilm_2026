<?php

namespace App\Repository;

use App\Entity\ServiceVideo;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceVideo>
 */
class ServiceVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceVideo::class);
    }

//    /**
//     * @return ServiceVideo[] Returns an array of ServiceVideo objects
//     */


    public function getMaxUpdatedAt(string $category): ?DateTimeImmutable
   {
       $date = $this->createQueryBuilder('s')
            ->select("MAX(s.updatedAt)")
            ->andWhere('s.category = :category')
            ->setParameter('category',$category)
           ->getQuery()
          ->getSingleScalarResult()
       ;

       return new DateTimeImmutable($date);
   }
   }
