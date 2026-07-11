<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\ServiceVideoRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        $response = new Response();
    
        $response->setPublic();
        $response->setMaxAge(86400);
        return $this->render("services/index.html.twig",[
          'route' => 'services'
        ],$response);
    }




     #[Route('/services/clip-video', name: 'app_services_clip_video')]
    public function serviceClipVideo(ProjectRepository $pr,TagAwareCacheInterface $cache,Request $request): Response
    {   
        $response = new Response();
        $response->setEtag(md5('clip-video-' . $pr->getMaxUpdateAt('clip-video')->getTimestamp()));
        $response->headers->set('Cache-Control', 'public, no-cache, must-revalidate');

        if ($response->isNotModified($request)) {
                return $response; 
        }


        $videos = $cache->get('clip-video',function (ItemInterface $item) use ($pr) {
          $item->expiresAfter(86400);
          $item->tag(['services-clip-video']);
          $rawVideos = $pr->findBy(['isActive' => 1],['orderIndex' => 'DESC']);

          $cachedVideos = [];

          foreach ($rawVideos as $video) {
              $cachedVideos[] = [
                'youtubeVideo' => $video->getYoutubeVideo(),
                'name' => $video->getName(),
              ];
          }

          return $cachedVideos;
        });

        $videos = $pr->findBy(['isActive' => 1],['orderIndex' => 'DESC']);
        


        return $this->render("services/serviceClipVideo.html.twig",[
          'route' => 'services',
          'videos' => $videos,
          'serviceName' => 'Clip Video'
        ],$response);
    }

    #[Route('/services/{category}', name: 'app_services_singleService')]
public function singleServicePage(string $category, ServiceVideoRepository $sv, LoggerInterface $adminLogger, Request $request, TagAwareCacheInterface $cache): Response
{   
    if ($category === 'clip-video') {
        return $this->redirect('/services/clip-video', 302);
    }
    
    $allowedCategories = ['publicitaire', 'corporate', 'evenementiel'];
    $isServiceExists = in_array($category, $allowedCategories);
    if (!$isServiceExists) {
        $adminLogger->alert('Service not found : name-> ' . $category);
        throw $this->createNotFoundException('Cette page n\'existe pas');
    }

    $response = new Response();
    $response->setEtag(md5('single-service-' . $category . '-' . $sv->getMaxUpdatedAt($category)->getTimestamp()));
    $response->headers->set('Cache-Control', 'public, no-cache, must-revalidate');

    if ($response->isNotModified($request)) {
        return $response; 
    }

    $videos = $cache->get('single-service-' . $category, function (ItemInterface $item) use ($sv, $category) {
        $item->expiresAfter(86400);
        $item->tag(['single-service-' . $category]);
        
        $rawVideos = $sv->findBy(['category' => $category], ['position' => 'ASC']);

        $cachedVideos = [];
        foreach ($rawVideos as $video) {
            $cachedVideos[] = [
                'videoLink' => $video->getVideoLink(),
                'isShort'      => $video->isShort(), 
                'title'     => $video->getTitle(),
                'isShort'   => $video->isShort()
            ];
        }

        return $cachedVideos;
    });

    return $this->render("services/singleService.html.twig", [
        'route'       => 'services',
        'videos'      => $videos,
        'serviceName' => $category
    ], $response);
}
}
