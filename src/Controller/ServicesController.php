<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\ServiceVideoRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        return $this->render("services/index.html.twig",[
          'route' => 'services'
        ]);
    }




     #[Route('/services/clip-video', name: 'app_services_clip_video')]
    public function serviceClipVideo(ProjectRepository $pr): Response
    {   
        $videos = $pr->findBy([],['orderIndex' => 'DESC']);
        return $this->render("services/serviceClipVideo.html.twig",[
          'route' => 'services',
          'videos' => $videos,
          'serviceName' => 'Clip Video'
        ]);
    }

      #[Route('/services/{category}', name: 'app_services_singleService')]
    public function singleServicePage(string $category,ServiceVideoRepository $sv, LoggerInterface $adminLogger): Response
    {   
        if($category === 'clip-video'){
            return $this->redirect('/services/clip-video',302);
        }
        $allowedCategories = ['publicitaire','corporate','evenementiel'];
        $isServiceExists = in_array($category,$allowedCategories);
        if (!$isServiceExists) {
            $adminLogger->alert('Service not found : name-> ' . $category);
            throw $this->createNotFoundException('Cette page n\'existe pas');
        }
        $videos = $sv->findBy(['category' => $category],['position' => 'ASC']);
        return $this->render("services/singleService.html.twig",[
          'route' => 'services',
          'videos' => $videos,
          'serviceName' => $category
        ]);
    }
}
