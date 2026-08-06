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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class ProjectController extends AbstractController
{
    #[Route('/projet/{slug}', name: 'app_project')]
    public function index(string $slug, ProjectRepository $repository,DeletedRepository $dr,Request $request,LoggerInterface $adminLogger,TagAwareCacheInterface $cache): Response
    {   
        $projectCheck = $repository->findOneBy(['slug' => $slug]);

        if ($projectCheck && !$projectCheck->isActive()) {
            $adminLogger->alert('Not Active Project in projet/{slug} : ' . $slug);
            throw new HttpException(403, "project_403");
        }
        
        if (!$projectCheck) {
            $deletedProject = $dr->findOneBy(["slug" => $slug]);
            if ($deletedProject) {
                $adminLogger->alert('Deleted Project in projet/{slug} : ' . $slug);
                throw new HttpException(410, "project_410");
            }
            $adminLogger->alert('Project not found in projet/{slug} : ' . $slug);
            throw new HttpException(404, "project_404");
        }
        $cookie = $request->cookies->get('cookie-consent', '');
        $response = new Response();
        $etag = md5($projectCheck->getId() . $projectCheck->getUpdatedAt()?->getTimestamp() . $cookie);
        $response->setETag($etag);
        $response->headers->set('Cache-Control', 'public, no-cache, must-revalidate');

        if ($response->isNotModified($request)) {
        return $response; 
    }
        $cacheKey = 'project-data-' . $projectCheck->getId();
        $projectData = $cache->get($cacheKey, function(ItemInterface $item) use ($repository, $projectCheck) {
            $item->expiresAfter(86400);
            $item->tag(['projects','project-' . $projectCheck->getId()]);
            $images = [];
            foreach ($projectCheck->getProjectImages() as $image) {
                $images[] = [
                    'id' => $image->getId(),
                    'src' => $image->getSrc(), 
                ];
            }

            $staff = $projectCheck->getProjectStaff(); 
            $createdStaff = [];
            if ($staff) {
                $createdStaff = json_decode($staff->getMoreStaffFields(), true) ?? [];
            }

            $nextQuery = $repository->createQueryBuilder('p')
                ->select('p.slug as next')
                ->where('p.orderIndex > :id')
                ->andWhere('p.isActive = 1')
                ->setParameter('id', $projectCheck->getOrderIndex())
                ->orderBy('p.orderIndex', 'ASC')
                ->setMaxResults(1)
                ->getQuery();
            
            $prevQuery = $repository->createQueryBuilder('p')
                ->select('p.slug as prev')
                ->where('p.orderIndex < :id')
                ->andWhere('p.isActive = 1')
                ->setParameter('id', $projectCheck->getOrderIndex())
                ->orderBy('p.orderIndex', 'DESC')
                ->setMaxResults(1)
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
          
            return [
                'images' => $images,
                'staff' => $staff,
                'createdStaff' => $createdStaff,
                'next' => $nextSlug,
                'prev' => $prevSlug
            ];
        });

    

        return  $this->render('project/index.html.twig', [
            'project' => $projectCheck,
            'images' => $projectData['images'],
            'staff' => $projectData['staff'],
            'createdStaff' => $projectData['createdStaff'],
            'next' => $projectData['next'],
            'prev' => $projectData['prev'],
            'cookie' => $cookie
        ],$response);

        
    }
}



