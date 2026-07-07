<?php

namespace App\Controller;

use App\Entity\Deleted;
use App\Entity\Gallery;
use App\Entity\GalleryImages;
use App\Entity\Project;
use App\Entity\ProjectImages;
use App\Entity\ProjectStaff;
use App\Entity\ServiceVideo;
use App\Form\AddGalleryImagesType;
use App\Form\CreateGalleryType;
use App\Form\DeleteImageType;
use App\Form\DeleteProjectType;
use App\Form\DeleteServiceVideoType;
use App\Form\ItemPositionType;
use App\Form\NewProjectType;
use App\Form\ServiceVideoType;
use App\Kernel;
use App\Repository\GalleryImagesRepository;
use App\Repository\GalleryRepository;
use App\Repository\ProjectImagesRepository;
use App\Repository\ProjectRepository;
use App\Repository\ServiceVideoRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class AdminController extends AbstractController
{
   
    public function __construct(private LoggerInterface $adminLogger,private SitemapController $sitemap)
    {
        
    }

     #[Route('/admin/home', name: 'app_admin_home')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            
        ]);
    }
    

     #[Route('/admin/newProject', name: 'app_admin_newProject')]
    public function newProject(Request $request,EntityManagerInterface $em,ProjectRepository $pr):Response
    {
        $project = new Project();
        $staff = new ProjectStaff();
        $project->setProjectStaff($staff);
  
        $form = $this->createForm(NewProjectType::class,$project,[
            'validation_groups' => ['Default', 'create'],
        ]);

        $form->handleRequest($request);
        $formErrors = $form->getErrors(true);

        

        if ($form->isSubmitted() && $form->isValid()) {
          
                $lastOrderIndex = $pr->createQueryBuilder('p')
                ->select('MAX(p.orderIndex)')->setMaxResults(1);
                $query = $lastOrderIndex->getQuery();
                $newOrderIndex = ($query->getSingleScalarResult() ?? 0) + 1;

                if ($form->has('isActive')) {
                    $project->setActive((bool)$form->get("isActive")->getData());
                }

                $project->setUpdatedAt(new DateTimeImmutable("now"));
                $project->setOrderIndex($newOrderIndex);

                $newStaffTitleArray = $_POST["new_staff_title"];
                $newStaffValueArray =  $_POST["new_staff_value"];
                $newStaff = [];
                foreach ($newStaffTitleArray as $key => $value) {
                    if($value && $newStaffValueArray[$key]){
                        $newStaff[$key] = [
                        'value1' => $value,
                        'value2' => $newStaffValueArray[$key]
                        ];
                    }
                }

                $encodedStaff = json_encode($newStaff);

                $staff->setMoreStaffFields($encodedStaff);

                $videoUID = 'video-' . bin2hex(random_bytes(18)) . ".mp4";
                $project->setBackgroundVideo($videoUID);

                $videoFile = $form->get("background_video")->getData();
    
                $imageNames = [];

                $imageFiles = [];
                for ($i = 1; $i <= 6; $i++) {
                    $imageFiles[$i] = $form->get('image' . $i)->getData();
                }

                foreach ($imageFiles as $key => $file) {
                    if ($file) {
                        $pImage = new ProjectImages();
                        $name = 'p-img' . $key . bin2hex(random_bytes(18)) . ".webp";
                        $imageNames[] = $name;
                        $pImage->setSrc($name);
                        $pImage->setProjectId($project);
                        if ($key === 1) {
                            $project->setThumb($name);
                        }
                        $em->persist($pImage);
                    }
                }

                $moved = [];
                try {
                 
                    $em->persist($project);
                    $em->persist($staff);
            
                    $em->flush();
               
                    if ($videoFile && $videoUID) {
                        $videoFile->move('uploads/videos/', $videoUID);
                        $moved[] = 'uploads/videos/' . $videoUID;
                    }

                    foreach ($imageFiles as $key => $img) {
                        if ($img && isset($imageNames[$key - 1])) {
                            $img->move('uploads/images/projects/', $imageNames[$key - 1]);
                            $moved[] = 'uploads/images/projects/' . $imageNames[$key - 1];
                        }
                    }

                    $this->sitemap->generateSitemap();
                    $this->addFlash('success',"Projet Créé");
                    return $this->redirectToRoute('app_admin_home');

                } catch (\Exception $th) {
                    $this->adminLogger->error($th->getMessage(),['error' => $th]);
                    $this->addFlash('error',$th->getMessage());

                    return $this->render('admin/newProject.html.twig', [
                        'form' => $form,
                        'formErrors' => $formErrors
                    ], new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY));
                }
       }
        return $this->render('admin/newProject.html.twig', [
            'form' => $form,
            'formErrors' => $formErrors 
        ]);
    }



     #[Route('/admin/projects', name: 'app_admin_projects')]
    public function projects(ProjectRepository $projectRepository,Request $request,EntityManagerInterface $em): Response
    {   
        $form = $this->createForm(ItemPositionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $P1ID = $form->get("item1ID")->getData();
            $P1Position = $form->get("item1Position")->getData();
            $P2ID = $form->get("item2ID")->getData();
            $P2Position = $form->get("item2Position")->getData();

            $p1 = $projectRepository->findOneBy(['id'=>$P1ID]);
            $p2 = $projectRepository->findOneBy(['id'=>$P2ID]);

            if ($p1 && $p2 && $P1Position && $P2Position) {
                $p1->setOrderIndex($P1Position);
                $p2->setOrderIndex($P2Position);
                $p1->setUpdatedAt(new DateTimeImmutable('now'));
                $p2->setUpdatedAt(new DateTimeImmutable('now'));
                try {
                    $em->flush();
                    $this->addFlash('success','Position modifiée');
                } catch (\Throwable $th) {
                    $this->addFlash('error',$th->getMessage());
                }
            }else{
                $this->addFlash('error',"Erreur pendant la modification");
            }
            return $this->redirectToRoute('app_admin_projects');
        }

        $projects = $projectRepository->findBy([],['orderIndex' => 'ASC']);

        return $this->render('admin/projects.html.twig', [
            'projects' => $projects,
            'form' => $form
        ]);
    }




     #[Route('/admin/projects/{id}', name: 'app_admin_edit_project')]
    public function editProjects(?Project $p, ProjectImagesRepository $pi, EntityManagerInterface $em, Request $request): Response
    {
        if(!$p){
            $this->addFlash('error',"Le Projet demandé n'existe pas");
            return $this->redirectToRoute('app_admin_projects');    
        }

        $form = $this->createForm(NewProjectType::class,$p);
        $deleteForm = $this->createForm(DeleteProjectType::class);

        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $slug = $p->getSlug();
            $em->remove($p);
            
            $deletedProject = new Deleted();
            $deletedProject->setSlug($slug);

            $em->persist($deletedProject);

            try {
                $em->flush();
                $this->sitemap->generateSitemap();
                $this->addFlash('success',"Le Projet a été supprimé");
            } catch (\Throwable $th) {
                $this->addFlash('error',"Le Projet demandé n'existe pas");
                return $this->redirectToRoute('app_admin_edit_project',['id' => $p->getId()]);
            }
           
            return $this->redirectToRoute('app_admin_projects');
        }
        
        $form->handleRequest($request);
        $formErrors = $form->getErrors(true);

        if (count($formErrors)) {
            $this->addFlash('error',$formErrors);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $filesToUnlink = [];
            $filesToMove = [];

            try {
                if ($form->has("isActive")) {
                    $p->setActive((bool)$form->get("isActive")->getData());
                }
                $p->setUpdatedAt(new DateTimeImmutable('now'));
                $videoFile = $form->get("background_video")->getData();
                if ($videoFile) {
                    $filesToUnlink[] = "uploads/videos/" . $p->getBackgroundVideo();
                    $videoUID = 'video-' . bin2hex(random_bytes(18)) . ".mp4";
                    $filesToMove[] = [
                            'path' => "uploads/videos/",
                            'name' =>  $videoUID,
                            'file' => $videoFile
                    ];
                    $p->setBackgroundVideo($videoUID);
                }
                
                $arrayImages = array_fill(1,6,"image");
                
                foreach ($arrayImages as $key => $name) {
                    $img = $form->get($name . $key)->getData();
                    if ($img) {
                        $lastImage = $form->get("lastImage" . $key)->getData();
                        $filesToUnlink[] = "uploads/images/projects/" . $lastImage;
                        $imageToEdit = $pi->findOneBy(["src" => $lastImage]);
                        $imageName = 'p-img1'  . bin2hex(random_bytes(18)) . ".webp";

                        if(!$imageToEdit){
                            $imageToEdit = new ProjectImages();
                            $imageToEdit->setProjectId($p);
                            $imageToEdit->setSrc($imageName);
                            $em->persist($imageToEdit);
                        }
                      
                        $filesToMove[] = [
                            'path' => "uploads/images/projects/",
                            'name' =>  $imageName,
                            'file' => $img
                        ];
                         $imageToEdit->setSrc($imageName);
                    }
                }

                $newStaffTitleArray = $_POST["new_staff_title"];
                $newStaffValueArray =  $_POST["new_staff_value"];
                $newStaff = [];

                foreach ($newStaffTitleArray as $key => $value) {
                    if($value && $newStaffValueArray[$key]){
                        $newStaff[$key] = [
                        'value1' => $value,
                        'value2' => $newStaffValueArray[$key]
                        ];
                    }
                }

                $encodedStaff = json_encode($newStaff);

                $p->getProjectStaff()->setMoreStaffFields($encodedStaff);
            
                $em->flush();

                foreach ($filesToUnlink as $key => $file) {
                    if (file_exists($file) && !is_dir($file)) {
                        unlink($file);
                    }
                }

                foreach ($filesToMove as $key => $fileToMove) {
                    $fileToMove['file']->move($fileToMove['path'],$fileToMove['name']);
                }

                $this->sitemap->generateSitemap();
                $this->addFlash('success',"Projet Modifié");
                return $this->redirectToRoute('app_admin_projects');

            } catch (\Throwable $th) {
                if($th->getCode() === 1062){
                        if (preg_match("/Duplicate entry '(.*?)'/", $th->getMessage(), $matches)) {
                            $duplicateValue = $matches[1]; 
                            $this->addFlash('error','La valeur ' . $duplicateValue . ' existe déjà');
                        }
                    }else{
                        $this->addFlash('error',$th->getMessage());
                    }
                    $this->adminLogger->error('Error:',['message' => $th->getMessage()]);

                return $this->render('admin/newProject.html.twig',[
                    'project' => $p,
                    'form' => $form,
                    'deleteForm' =>$deleteForm,
                    'moreStaff' => json_decode($p->getProjectStaff()->getMoreStaffFields()) 
                ], new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY));
            }
        }

        return $this->render('admin/newProject.html.twig', [
            'project' => $p,
            'form' => $form,
            'deleteForm' =>$deleteForm,
            'moreStaff' => json_decode($p->getProjectStaff()->getMoreStaffFields()) 
        ]);

    }



     #[Route('/admin/galleries', name: 'app_admin_galleries')]
    public function galleries(GalleryRepository $gr,Request $request,EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ItemPositionType::class);
        $form->handleRequest($request);
        $galleries = $gr->findBy([],['position' => "ASC"]);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $img1ID = $form->get("item1ID")->getData();
            $img1Position = $form->get("item1Position")->getData();
            $img2ID = $form->get("item2ID")->getData();
            $img2Position = $form->get("item2Position")->getData();

            $img1 = $gr->findOneBy(['id'=>$img1ID]);
            $img2 = $gr->findOneBy(['id'=>$img2ID]);

            if ($img1 && $img2 ) {
                $img1->setPosition($img1Position);
                $img2->setPosition($img2Position);
                try {
                    $em->flush();
                } catch (\Throwable $th) {
                    $this->adminLogger->error('Error:',['message' => $th->getMessage()]);
                    $this->addFlash('error',$th->getMessage());

                    return $this->render('admin/galleries.html.twig',[
                        'galleries' => $galleries,
                        'form' => $form
                    ], new Response("eee", Response::HTTP_UNPROCESSABLE_ENTITY));
                }
               
                return $this->redirectToRoute('app_admin_galleries');
            }

        }
        return $this->render('admin/galleries.html.twig',[
            'galleries' => $galleries,
            'form' => $form
        ]);
    }


    #[Route('/admin/galleries/{name}', name: 'app_admin_gallery')]
    public function gallery(string $name, GalleryImagesRepository $gr,Request $request,EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ItemPositionType::class);
        $deleteForm = $this->createForm(DeleteImageType::class);
        
        $deleteForm->handleRequest($request);

        if($deleteForm->isSubmitted() && $deleteForm->isValid()){
            $id = $deleteForm->get("imageID")->getData();
            $image = $gr->findOneBy(['id'=>$id]);
            $imageSrc = $image->getSrc();
            $em->remove($image);
            $image->getGallery()->setUpdatedAt();

            try {
                $em->flush();
                if (file_exists("uploads/images/galleries/" . $imageSrc)) {
                    unlink("uploads/images/galleries/" . $imageSrc);
                }
                $this->addFlash('success','Supprimée');
            } catch (\Throwable $th) {
                $this->addFlash('error',$th->getMessage());
            }
           
            return $this->redirectToRoute('app_admin_gallery',['name'=> $name]);
        }
       

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $img1ID = $form->get("item1ID")->getData();
            $img1Position = $form->get("item1Position")->getData();
            $img2ID = $form->get("item2ID")->getData();
            $img2Position = $form->get("item2Position")->getData();

            $img1 = $gr->findOneBy(['id'=>$img1ID]);
            $img2 = $gr->findOneBy(['id'=>$img2ID]);

            if ($img1 && $img2 ) {
                $img1->setPosition($img1Position);
                $img2->setPosition($img2Position);
                $img1->getGallery()->setUpdatedAt();
                try {
                    $em->flush();
                    $this->addFlash('success','Position modifièe');
                } catch (\Throwable $th) {
                    $this->addFlash('error',$th->getMessage());
                }
                return $this->redirectToRoute('app_admin_gallery',['name'=> $name]);
            }
        }
        
        $images = $gr->findByGalleryName($name);

        return $this->render('admin/gallery.html.twig',[
            'images' => $images,
            'form' => $form,
            'deleteForm' => $deleteForm,
            'galleryName' => $name
        ]);
    }


    #[Route('/admin/newGallery', name: 'app_admin_newGallery')]
    public function newGallery(Request $request, EntityManagerInterface $em, GalleryRepository $gr): Response
    {
        $form = $this->createForm(CreateGalleryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gallery = new Gallery();
            $name = $form->get("name")->getData();
            $image = $form->get("image")->getData();
            $imageName = "g-img-" . bin2hex(random_bytes(18)) . ".webp";
            
            $gallery->setName($name);
            $gallery->setSrc($imageName);

            $position = $gr->createQueryBuilder('g')
            ->select('MAX(g.position)')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

            $gallery->setPosition(($position ?? 0) + 1);

            $em->persist($gallery);

            $this->sitemap->updatePage('/galerie');

            try {
                $em->flush();
                $this->sitemap->generateSitemap();
                $image->move("uploads/images/gallery/",$imageName);
            } catch (\Throwable $th) {
                $this->addFlash('error',$th->getMessage());
                return $this->render('admin/newGallery.html.twig',[
                    'form' => $form
                ],new Response(null,500));
            }
            $this->addFlash('success',"Galerie Créé");
            return $this->redirectToRoute('app_admin_galleries');
        }

        return $this->render('admin/newGallery.html.twig',[
            'form' => $form
        ]);
    }


    #[Route('/admin/galleries/{name}/edit', name: 'app_admin_edit_gallery')]
    public function editGallery(string $name,Request $request, EntityManagerInterface $em, GalleryRepository $gr): Response
    {
        $gallery = $gr->findOneBy(['name' => $name]);
        $form = $this->createForm(CreateGalleryType::class,$gallery);

        $deleteForm = $this->createFormBuilder()
        ->setAction($this->generateUrl("app_admin_delete_gallery",['id' => $gallery->getId()]))
        ->setMethod('POST')
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newName = $form->get("name")->getData();

            if ($newName !== $name) {
                $gallery->setName($newName);
                $name = $newName;
            }

            $image = $form->get('image')->getData();
            $imageSrc = "g-" . bin2hex(random_bytes(18)) . ".webp"; 

            if ($image) {
                $lastImage = $form->get("lastImage")->getData();
                $gallery->setSrc($imageSrc);
            }

            $gallery->setUpdatedAt();

            try {
                $em->flush();
                if ($image) {
                    if (file_exists("uploads/images/gallery/" . $lastImage)) {
                    unlink("uploads/images/gallery/" . $lastImage);
                }
                    $image->move("uploads/images/gallery/",$imageSrc);
                }
                $this->sitemap->updatePage('/galerie');
                $this->sitemap->generateSitemap();
                $this->addFlash('success','Galerie modifiée');
                return $this->redirectToRoute('app_admin_galleries');
                
            } catch (\Throwable $th) {
                $this->addFlash('error',$th->getMessage());
                return $this->render('admin/newGallery.html.twig',[
                'form' => $form,
                'gallery' => $gallery,
                'deleteForm' => $deleteForm,
                'formErrors' => $form->getErrors(true)
                ],new Response(null,500));
            }
        }

        return $this->render('admin/newGallery.html.twig',[
            'form' => $form,
            'gallery' => $gallery,
            'deleteForm' => $deleteForm,
            'formErrors' => $form->getErrors(true)
        ]);
    }

    #[Route('/admin/galleries/{id}/delete', name: 'app_admin_delete_gallery', methods: ['POST'])]
     public function deleteGallery(Gallery $gallery, EntityManagerInterface $em): Response
     {
        $images = $gallery->getImages();
        $em->remove($gallery);
        $imgSrc = $gallery->getSrc();
        try {
           $em->flush();

            if(file_exists("uploads/images/gallery/" . $imgSrc )){
                unlink("uploads/images/gallery/" . $imgSrc );
            }

            foreach ($images as $key => $img) {
                $imgPath = "uploads/images/galleries/" . $img->getSrc();

                if (file_exists($imgPath)) {
                    unlink($imgPath);
                }
            }
        $this->sitemap->updatePage('/galerie');
        $this->sitemap->generateSitemap();
        } catch (\Throwable $th) {
            $this->addFlash('error',$th->getMessage());
        }
        return $this->redirectToRoute('app_admin_galleries');
     }


     #[Route('/admin/gallery/{name}/add-images', name: 'app_admin_gallery_add_images', methods: ['GET','POST'])]
     public function galleryAddImages(string $name, Request $request,EntityManagerInterface $em,GalleryRepository $gr,GalleryImagesRepository $gi): Response
     {
        $form = $this->createForm(AddGalleryImagesType::class);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gallery = $gr->findOneBy(['name' => $name]);   
            $images = $form->get("files")->getData();
            $imgNames = [];
            $filesToMove = [];
            $lastPosition = $gi->createQueryBuilder('g')
            ->select('MAX(g.position)')
            ->where("g.gallery = :id")
            ->setParameter("id",$gallery->getId())
            ->setMaxResults(1);
            $query = $lastPosition->getQuery();
            $position = ($query->getSingleScalarResult() ?? 0) + 1;

            foreach ($images as $key => $img) {
                $galleryImage = new GalleryImages();
                $src = "g-img-" . bin2hex(random_bytes(18)) . ".webp";
                $imgNames[] = $src;
                $filesToMove[] = $img;
                $galleryImage->setSrc($src);
                $galleryImage->setGallery($gallery);
                $galleryImage->setPosition($position + (count($images)) - $key);
                $gallery->addImage($galleryImage);
                $em->persist($galleryImage);
            }

            $gallery->setUpdatedAt();

            try {
                $em->flush();

                foreach ($filesToMove as $key => $file) {
                    $file->move("uploads/images/galleries/",$imgNames[$key]);
                }   
                $this->sitemap->generateSitemap();

                return $this->redirectToRoute('app_admin_gallery',[
                    'name' => $name
                ]);

            } catch (\Throwable $th) {
                return $this->render("admin/galleryImagesAdd.html.twig",[
                    'form' => $form,
                    'error' => $th->getMessage(),
                    'name' => $name
                ]);
            }
        }

        return $this->render("admin/galleryImagesAdd.html.twig",[
            'form' => $form,
            'name' => $name
        ]);
     }

     #[Route('/admin/logs', name: 'app_admin_logs')]
    public function logs(Kernel $kernel): Response
    {
        $path = $kernel->getProjectDir() . '/var/log/admin.log';
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $entries = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $entries[] = $data;
            } else {
                $entries[] = ['message' => $line];
            }
        }
        
        return $this->render('admin/logs.html.twig', [
            'array' => $entries
        ]);
    }


    //Services
    #[Route('/admin/services', name: 'app_admin_services')]
    public function services(ServiceVideoRepository $sv,Request $request,EntityManagerInterface $em): Response
    {
        return $this->render('admin/services.html.twig', [
          
        ]);
    }

    #[Route('/admin/services/{name}', name: 'app_admin_single_services')]
    public function singleService(string $name,ServiceVideoRepository $sv,Request $request,EntityManagerInterface $em): Response
    {
        $videos = $sv->findBy(['category' => $name],['position' => "ASC"]);
        $deleteForm = $this->createForm(DeleteServiceVideoType::class);
        $form = $this->createForm(ItemPositionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $P1ID = $form->get("item1ID")->getData();
            $P1Position = $form->get("item1Position")->getData();
            $P2ID = $form->get("item2ID")->getData();
            $P2Position = $form->get("item2Position")->getData();

            $p1 = $sv->findOneBy(['id'=>$P1ID]);
            $p2 = $sv->findOneBy(['id'=>$P2ID]);

            if ($p1 && $p2 && $P1Position && $P2Position) {
                $p1->setPosition($P1Position);
                $p2->setPosition($P2Position);
                try {
                    $em->flush();
                    $this->addFlash('success','Position modifiée');
                } catch (\Throwable $th) {
                    $this->addFlash('error',$th->getMessage());
                }
            }else{
                $this->addFlash('error',"Erreur pendant la modification: Variable Position Manquante");
            }
            return $this->redirectToRoute('app_admin_single_services',['name' => $name]);
        }

        return $this->render('admin/singleService.html.twig', [
            'videos' => $videos,
            'positionForm' => $form,
            'serviceName' => $name,
            'deleteForm' => $deleteForm
        ]);
    }


     #[Route('/admin/services/{serviceName}/new', name: 'app_admin_services_new')]
    public function newService(string $serviceName, Request $request, EntityManagerInterface $em,ServiceVideoRepository $sv): Response
    {   
        $video = new ServiceVideo();
        $form = $this->createForm(ServiceVideoType::class,$video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lastVideo = $sv->findOneBy(['category' => $serviceName],['position' => 'DESC']);
            $lastPosition = $lastVideo ?  $lastVideo->getPosition() : 0;
            $video->setIsShort((bool)$form->get('isShort')->getData());
            $video->setPosition($lastPosition + 1);
            $em->persist($video);

            try {
                 $em->flush();
                 $this->addFlash('success','Créé');
                 return $this->redirectToRoute('app_admin_services_new',['serviceName' => $serviceName]);
            } catch (\Throwable $th) {
                $this->addFlash('error',$th->getMessage());
                 return $this->render('admin/newService.html.twig', [
                    'form' => $form,
                    'serviceName' => $serviceName
                ],new Response(null,Response::HTTP_UNPROCESSABLE_ENTITY));
            }
        }
       
        return $this->render('admin/newService.html.twig', [
            'form' => $form,
            'serviceName' => $serviceName
        ]);
    }


    #[Route('/admin/services/{id}/edit', name: 'app_admin_services_editVideo',methods:['GET','POST'])]
     public function editServiceVideo(?ServiceVideo $video, EntityManagerInterface $em, Request $request):Response
     {
        if (!$video) {
            return $this->redirectToRoute('app_admin_services');
        }
            $form = $this->createForm(ServiceVideoType::class,$video);
            $form->handleRequest($request);
            $category = $video->getCategory();
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                $this->addFlash('success','Video Modifié');
                return $this->redirectToRoute('app_admin_single_services',['name' => $category]);
            }
           
            return $this->render('admin/newService.html.twig', [
                'form' => $form,
                'serviceName' => $category
            ]);
     }

    #[Route('/admin/services/{id}/delete', name: 'app_admin_services_deleteVideo',methods:['POST'])]
     public function deleteServiceVideo(?ServiceVideo $video, EntityManagerInterface $em, Request $request):RedirectResponse
     {
        if (!$video) {
            return $this->redirectToRoute('app_admin_services');
        }
            $form = $this->createForm(DeleteServiceVideoType::class);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $em->remove($video);
                $em->flush();
                $this->addFlash('success', 'Video effacée.');
            }
            $category = $video->getCategory();
        return $this->redirectToRoute('app_admin_single_services',['name' => $category]);
     }
}
