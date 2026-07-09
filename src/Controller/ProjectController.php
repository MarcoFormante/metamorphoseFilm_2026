<?php

namespace App\Controller;

use App\Repository\DeletedRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/projet/{slug}', name: 'app_project')]
    public function index(string $slug, ProjectRepository $repository,DeletedRepository $dr,Request $request,LoggerInterface $adminLogger): Response
    {
        
        $project = $repository->findOneBy(['slug' => $slug]);
        $cookie = $request->cookies->get('cookie-consent','');
    
        if ($project && !$project->isActive()) {
            $adminLogger->alert('Not Active Project in projet/{slug} : ' . $slug);
            throw new HttpException(403,"project_403");
        }

        if (!$project) {
            $deletedProject = $dr->findOneBy(["slug" => $slug]);

            if ($deletedProject) {
                $adminLogger->alert('Deleted Project in projet/{slug} : ' . $slug);
                throw new HttpException(410,"project_410");
            }
            $adminLogger->alert('Project not found in projet/{slug} : ' . $slug);
            throw new HttpException(404,"project_404");
        }

        

        $etagVersion = $slug . '_' . $project->getUpdatedAt()->getTimestamp() . $cookie ;
        $etag = md5($etagVersion);

        $response = new Response();
        $response->setEtag($etag);
        $response->setPrivate();
        $response->setMaxAge(3600);
        $response->setVary('Cookie');

        if ($response->isNotModified($request)) {
            return $response;
        }


        $images = $project->getProjectImages();
        $staff = $project->getProjectStaff(); 

        if ($staff) {
            $createdStaff = json_decode($staff->getMoreStaffFields(),true);
        }

        $nextQuery = $repository->createQueryBuilder('p')
        ->select('p.slug as next')
        ->where('p.orderIndex > :id' )
        ->andWhere('p.isActive = 1')
        ->setParameter('id',$project->getOrderIndex())
        ->orderBy('p.orderIndex', 'ASC')
        ->setMaxResults(1)
        ->setFirstResult(0)
        ->getQuery();
        
       
    
        $prevQuery = $repository->createQueryBuilder('p')
        ->select('p.slug as prev')
        ->where('p.orderIndex < :id' )
        ->andWhere('p.isActive = 1')
        ->setParameter('id',$project->getOrderIndex())
        ->orderBy('p.orderIndex', 'DESC')
        ->setMaxResults(1)
        ->setFirstResult(0)
        ->getQuery();
       
        try {
            $nextSlug = $nextQuery->getSingleScalarResult();
        } catch (NoResultException) {
            $nextSlug = null;
        }

        try {
            $prevSlug = $prevQuery->getSingleScalarResult();
        } catch (NoResultException) {
            $prevSlug = null;
        }
      

        return $this->render('project/index.html.twig', [
            'project' => $project,
            'images' => $images,
            'staff' => $staff,
            'createdStaff' => $createdStaff ?? [],
            'next' => $nextSlug,
            'prev' => $prevSlug,
            'cookie' => $cookie
        ],$response);
    }
}



