<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrivacyController extends AbstractController
{
    #[Route('/privacy-policy/{lang}', name: 'app_privacy')]
    public function index(string $lang): Response
    {
        if (in_array($lang,['fr','en'])) {
           
            return $this->render("privacy/$lang.html.twig", [
               'route'=> 'privacy-policy'
            ]);
        }else{
            return $this->render('home/index.html.twig', [
               'route'=> 'home'
            ]);
        }
    }
}
