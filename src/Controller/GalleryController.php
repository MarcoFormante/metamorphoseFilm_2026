<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Repository\GalleryImagesRepository;
use App\Repository\GalleryRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GalleryController extends AbstractController
{
    
    #[Route('/galerie', name: 'app_gallery')]
    public function index(GalleryRepository $gr): Response
    {
        $galleries = $gr->findBy([],['position' => 'ASC']);

        return $this->render('gallery/index.html.twig', [
            'route' => 'galerie',
            'galleries' => $galleries
        ]);
    }


  #[Route('/galerie/{name}', name: 'app_gallery_images')]
    public function getGalleryImages(#[MapEntity(mapping: ['name' => 'name'])] ?Gallery $gallery,string $name,GalleryImagesRepository $gr): Response
    {
        if (!$gallery) {
            throw $this->createNotFoundException("gallery");
        }

        $images = $gr->findBy(["gallery" => $gallery],['position' => 'DESC']);


        return $this->render('gallery/galleryImages.html.twig', [
            'route' => 'galerie',
            'images' => $images,
            'name' => $name
        ]);
    }
}
