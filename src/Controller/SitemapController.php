<?php
namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Pages;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use samdark\sitemap\Sitemap;

class SitemapController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
       
    }

    public function generateSitemap( )
    {
        try {
            $isDev = $_ENV["APP_ENV"] === "dev" ? true : false;
            $hostname = 'https://metamorphosefilm.com';
            $sitemapPath = $this->getParameter('kernel.project_dir') . ($isDev ? '/public/sitemap.xml' : "/public_html/sitemap.xml");
            $sitemap = new Sitemap($sitemapPath);
    
            // Static pages
           $pages = $this->em->getRepository(Pages::class)->findBy(["isActive"=>1]);
            foreach($pages as $page){
                $sitemap->addItem(
                    $hostname . $page->getPage(),
                    $page->getUpdatedAt()->getTimestamp(),
                    Sitemap::MONTHLY,
                    $page->getPriority()
                );
            }
            
            //Videos
                $projects = $this->em->getRepository(Project::class)->findBy(["isActive"=>1]);
                foreach ($projects as $project) {
                    $sitemap->addItem(
                        $hostname . "/projet/" .  $project->getSlug(),
                        $project->getUpdatedAt()->getTimestamp(),
                        Sitemap::MONTHLY,
                        0.8
                    );
                }

            //Galleries

                $galleries = $this->em->getRepository(Gallery::class)->findAll();
                 foreach ($galleries as $gallery) {
                    $sitemap->addItem(
                        $hostname . "/galerie/" .  strtolower($gallery->getName()),
                        $gallery->getUpdatedAt()->getTimestamp(),
                        Sitemap::MONTHLY,
                        0.8
                    );
                }

            
            $sitemap->write();
            $this->addFlash('success','SiteMap mis à jour');
    
            return true;
        } catch (\Throwable $th) {
            return false;
        }
       
    }


    public function updatePage(string $page, $priority = null, $isActive = null)
    {
        try {
            $pageRepository = $this->em->getRepository(Pages::class);
            $pageExists = $pageRepository->findOneBy(["page" => $page]);
            if ($pageExists) {
                $pageExists->setUpdatedAt(new \DateTimeImmutable());
                $pageExists->setActive($isActive ? $isActive : $pageExists->isActive());
                $pageExists->setPriority($priority ? $priority : $pageExists->getPriority());
                $this->em->persist($pageExists);
               
            } else {
                $pages = new Pages();
                $pages->setPage($page);
                $pages->setUpdatedAt(new \DateTimeImmutable());
                $pages->setActive($isActive);
                $pages->setPriority($priority);
                $this->em->persist($pages);
            }
            return true;
        } catch (\Throwable $th) {
             
            return false;
        }
    }
}