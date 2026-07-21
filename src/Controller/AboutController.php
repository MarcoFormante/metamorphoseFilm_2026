<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/a-propos', name: 'app_about')]
    public function index(Request $request): Response
    {
        $templatePath = $this->getParameter('kernel.project_dir') . '/templates/a_propos/index.html.twig';
        $lastModified = file_exists($templatePath)
            ? (new \DateTimeImmutable())->setTimestamp(filemtime($templatePath))
            : new \DateTimeImmutable();

        $response = new Response();
        $response->setLastModified($lastModified);
        $response->setETag(md5($lastModified->getTimestamp()));
        $response->setPublic();
        $response->setMaxAge(3600);
        $response->setSharedMaxAge(86400);

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $this->render("a_propos/index.html.twig",[
          'route' => 'a-propos'
        ], $response);
    }
}
