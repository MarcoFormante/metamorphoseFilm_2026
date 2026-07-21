<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrivacyController extends AbstractController
{
    #[Route('/privacy-policy/{lang}', name: 'app_privacy')]
    public function index(string $lang, LoggerInterface $adminLogger): Response
    {
        if (in_array($lang,['fr','en'])) {
            $response = $this->render("privacy/$lang.html.twig", [
                'route'=> 'privacy-policy'
            ]);
            $response->setPublic();
            $response->setMaxAge(86400); 
            $response->setSharedMaxAge(86400);

            return $response;
        } else {
            $adminLogger->alert('User searched bad Language in privacy-policy page');
            return $this->render('home/index.html.twig', [
                'route'=> 'home'
            ]);
        }
    }
}
