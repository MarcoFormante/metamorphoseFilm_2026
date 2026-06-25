<?php

namespace App\Controller;

use App\Repository\DeletedRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/projet/{slug}', name: 'app_project')]
    public function index(string $slug, ProjectRepository $repository,DeletedRepository $dr): Response
    {
        $project = $repository->findOneBy(['slug' => $slug]);


        if ($project && !$project->isActive()) {
            throw new HttpException(403,"project_403");
        }

        if (!$project) {
            $deletedProject = $dr->findOneBy(["slug" => $slug]);

            if ($deletedProject) {
                throw new HttpException(410,"project_410");
            }

            throw new HttpException(404,"project_404");
        }



        $images = $project->getProjectImages();
        $staff = $project->getProjectStaff(); 

        if ($staff) {
            $createdStaff = json_decode($staff->getMoreStaffFields(),true);
        }

        $nextQuery = $repository->createQueryBuilder('p')
        ->select('p.slug as next')
        ->where('p.orderIndex > :id' )
        ->setParameter('id',$project->getOrderIndex())
        ->orderBy('p.orderIndex', 'ASC')
        ->setMaxResults(1)
        ->setFirstResult(0)
        ->getQuery();
        
       
    
        $prevQuery = $repository->createQueryBuilder('p')
        ->select('p.slug as prev')
        ->where('p.orderIndex < :id' )
        ->setParameter('id',$project->getOrderIndex())
        ->orderBy('p.orderIndex', 'DESC')
        ->setMaxResults(1)
        ->setFirstResult(0)
        ->getQuery();
       
        try {
            $nextSlug = $nextQuery->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException) {
            $nextSlug = null;
        }

        try {
            $prevSlug = $prevQuery->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException) {
            $prevSlug = null;
        }

        return $this->render('project/index.html.twig', [
            'project' => $project,
            'images' => $images,
            'staff' => $staff,
            'createdStaff' => $createdStaff ?? [],
            'next' => $nextSlug,
            'prev' => $prevSlug
        ]);
    }
}



