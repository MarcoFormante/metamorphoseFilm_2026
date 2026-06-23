<?php

namespace App\Repository;

use App\Entity\GalleryImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GalleryImages>
 */
class GalleryImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryImages::class);
    }

    /**
     * Find images by gallery name (joins the Gallery relation)
     *
     * @param string $name
     * @return GalleryImages[]
     */
    public function findByGalleryName(string $name): array
    {
        return $this->createQueryBuilder('gi')
            ->innerJoin('gi.gallery', 'g')
            ->andWhere('g.name = :name')
            ->setParameter('name', $name)
            ->orderBy('gi.position', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
