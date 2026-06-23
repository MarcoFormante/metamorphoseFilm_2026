<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProjectRepository $projectRepository,Request $request,): Response
    {
      
        $count = $projectRepository->count([]);
        $lastUpdate = $projectRepository->getMaxUpdateAt();
        $etag = md5($count . '-' . $lastUpdate?->format("U") ?? '0');

        $response = new Response();
        $response->setEtag($etag);
        $response->setPublic();
        $response->setMaxAge(3600);


        if ($response->isNotModified($request)) {
            return $response;
        }

        $projectList = $projectRepository->findBy(
            ['isActive' => true],
            ['orderIndex' => 'ASC']
        );

        $response = $this->render('home/index.html.twig', [
                    'route' => '/',
                    'projects' => $projectList
                ]);
        
        $response->setEtag($etag);
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}


 