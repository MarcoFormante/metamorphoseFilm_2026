<?php

namespace App\Repository;

use App\Entity\Project;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }


   public function getMaxUpdateAt(): ?DateTimeImmutable
   {
       $date = $this->createQueryBuilder('p')
            ->select("MAX(p.updatedAt)")
           ->getQuery()
          ->getSingleScalarResult()
       ;

       return new DateTimeImmutable($date);
   }


}
