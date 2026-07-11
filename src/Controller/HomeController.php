<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProjectRepository $projectRepository,Request $request,TagAwareCacheInterface $cache): Response
    {
        
            $response = new Response();
            $etag = md5('projects-' . $projectRepository->getMaxUpdateAt()->getTimestamp());
            $response->setETag($etag);
            $response->headers->set('Cache-Control', 'public, no-cache, must-revalidate');

            if ($response->isNotModified($request)) {
                return $response; 
            }

            $cacheKey = 'home_projects';

            $projectList = $cache->get($cacheKey, function(ItemInterface $item) use ( $projectRepository){
                $item->expiresAfter(86400);
                $item->tag(['home-projects']);
                $rawProjects =  $projectRepository->findBy(
                ['isActive' => true],
                ['orderIndex' => 'ASC']);
                $cachedProjects = [];
                foreach ($rawProjects as $project ) {
                    $cachedProjects[] = [
                        'backgroundVideo' => $project->getBackgroundVideo(),
                        'slug' => $project->getSlug(),
                        'name' => $project->getName(),
                        'collab' => $project->getCollabWith(),
                        'thumb' => $project->getThumb()
                    ];
                }

                return $cachedProjects;
        });

        return $this->render('home/index.html.twig', [
                'route' => '/',
                'projects' => $projectList
        ],$response);
    }
    }


 