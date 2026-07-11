<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Repository\GalleryImagesRepository;
use App\Repository\GalleryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class GalleryController extends AbstractController
{

    public function __construct(private TagAwareCacheInterface $cache)
    {
    }
    
    #[Route('/galerie', name: 'app_gallery')]
    public function index(GalleryRepository $gr, Request $request): Response
    {
        $response = new Response();
        $etag = md5('galerie-' . $gr->getMaxUpdateAt()->getTimestamp());
        $response->setETag($etag);
       
         
        if ($response->isNotModified($request)) {
            return $response; 
        }
            $galleries = $this->cache->get('galerie', function(ItemInterface $item) use ( $gr){
            $item->expiresAfter(86400);
            $item->tag(['galerie']);
            $rawGalleries = $gr->findBy([],['position' => 'ASC']);
            $cachedGalleries = [];
            foreach ($rawGalleries as $gallery) {
                $cachedGalleries[] = [
                    'name' => $gallery->getName(),
                    'src' => $gallery->getSrc(),
                ];

            }
            return $cachedGalleries;
        });

        return $this->render('gallery/index.html.twig', [
            'route' => 'galerie',
            'galleries' => $galleries
        ],$response);
       
    }


  #[Route('/galerie/{name}', name: 'app_gallery_images')]
    public function getGalleryImages(#[MapEntity(mapping: ['name' => 'name'])] ?Gallery $gallery,string $name,GalleryImagesRepository $gr, LoggerInterface $adminLogger,Request $request): Response
    {
        if (!$gallery) {
            $adminLogger->alert('User searched bad gallery name in url: galerie/{name}');
            throw $this->createNotFoundException("gallery");
        }

        $response = new Response();
        $etag = md5('galerie-' . $gallery->getName() . '-' . $gallery->getUpdatedAt()->getTimestamp());
        $response->setETag($etag);
        $response->headers->set('Cache-Control', 'public, no-cache, must-revalidate');

        if ($response->isNotModified($request)) {
            return $response; 
        }
        $galleryId = $gallery->getName();
        $images = $this->cache->get('galerie-' . $galleryId, function(ItemInterface $item) use ($gr,$gallery,$galleryId) {
            $item->expiresAfter(86400);
            $item->tag(['galerie-' . strtolower($galleryId)]);
            $rawImages = $gr->findBy(["gallery" => $gallery],['position' => 'DESC']);
            $cachedImages = [];
            foreach ($rawImages as $image) {
                $cachedImages[] = [
                    'id' => $image->getId(),
                    'src' => $image->getSrc(), 
                    'description' => $image->getDescription(),
                ];
            }

            return $cachedImages;
        
        });
      


        return $this->render('gallery/galleryImages.html.twig', [
            'route' => 'galerie',
            'images' => $images,
            'name' => $name
        ],$response);
    }
}
