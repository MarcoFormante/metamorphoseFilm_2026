<?php

namespace App\Controller;

use App\Entity\Deleted;
use App\Entity\Gallery;
use App\Entity\GalleryImages;
use App\Entity\Project;
use App\Entity\ProjectImages;
use App\Entity\ProjectStaff;
use App\Form\AddGalleryImagesType;
use App\Form\CreateGalleryType;
use App\Form\DeleteImageType;
use App\Form\DeleteProjectType;
use App\Form\ItemPositionType;
use App\Form\NewProjectType;
use App\Repository\GalleryImagesRepository;
use App\Repository\GalleryRepository;
use App\Repository\ProjectImagesRepository;
use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class AdminController extends AbstractController
{
   

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
        $errors = [];

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

                } catch (\Exception $th) {
                    $errors = [$th->getMessage()];
                   
                    return $this->render('admin/newProject.html.twig', [
                        'form' => $form,
                        'errors' => $errors
                    ], new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY));
                }
            return $this->redirectToRoute("app_admin_newProject");
       }
    
        return $this->render('admin/newProject.html.twig', [
            'form' => $form,
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

                $em->flush();

                return $this->redirectToRoute('app_admin_projects');
            }

        }

        $projects = $projectRepository->findBy([],['orderIndex' => 'ASC']);

        return $this->render('admin/projects.html.twig', [
            'projects' => $projects,
            'form' => $form
        ]);
    }




     #[Route('/admin/projects/{id}', name: 'app_admin_edit_project')]
    public function editProjects(Project $p, ProjectImagesRepository $pi, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(NewProjectType::class,$p);
        $deleteForm = $this->createForm(DeleteProjectType::class);

        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $slug = $p->getSlug();
            $em->remove($p);
            
            $deletedProject = new Deleted();
            $deletedProject->setSlug($slug);

            $em->persist($deletedProject);

            $em->flush();

            return $this->redirectToRoute('app_admin_projects',[
                    'project' => $p,
                    'form' => $form,
                    'deleteForm' =>$deleteForm,
                    'moreStaff' => json_decode($p->getProjectStaff()->getMoreStaffFields()) 
            ]);

        }
        
        $form->handleRequest($request);

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

                return $this->redirectToRoute('app_admin_projects',[
                     'project' => $p,
                    'form' => $form,
                    'deleteForm' =>$deleteForm,
                    'moreStaff' => json_decode($p->getProjectStaff()->getMoreStaffFields()) 
                ]);

            } catch (\Throwable $th) {

                dd($th);
                return $this->render('admin/newProject.html.twig',[
                     'project' => $p,
                    'form' => $form,
                    'deleteForm' =>$deleteForm,
                    'moreStaff' => json_decode($p->getProjectStaff()->getMoreStaffFields()) 
                ], new Response("eee", Response::HTTP_UNPROCESSABLE_ENTITY));
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
      

        $form= $this->createForm(ItemPositionType::class);
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
                $em->flush();
                return $this->redirectToRoute('app_admin_galleries');
            }

        }

        $galleries = $gr->findBy([],['position' => "ASC"]);
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

        if($deleteForm->isSubmitted() && $deleteForm->isValid() && $deleteForm->getConfig()->getMethod() === "POST"){
            $id = $deleteForm->get("imageID")->getData();
            $image = $gr->findOneBy(['id'=>$id]);
            $imageSrc = $image->getSrc();
            $em->remove($image);
            $em->flush();

            if (file_exists("uploads/images/galleries/" . $imageSrc)) {
                unlink("uploads/images/galleries/" . $imageSrc);
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
                $em->flush();

                return $this->redirectToRoute('app_admin_gallery',['name'=> $name]);
            }

        }
        
            // fetch images by gallery name using repository helper
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

            try {
                $em->flush();
            } catch (\Throwable $th) {

                return $this->render('admin/newGallery.html.twig',[
                    'form' => $form,
                    'error' => $th->getMessage()
                ],new Response(null,500));
            }
         

            $image->move("uploads/images/gallery/",$imageName);

            return $this->redirectToRoute('app_admin_newGallery');
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
        $error = "";

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
            try {
                $em->flush();
                if ($image) {
                    if (file_exists("uploads/images/gallery/" . $lastImage)) {
                    unlink("uploads/images/gallery/" . $lastImage);
                }
                    $image->move("uploads/images/gallery/",$imageSrc);
                }

                return $this->redirectToRoute('app_admin_galleries');
                
            } catch (\Throwable $th) {
                $error = $th->getMessage();
            }
        }

        return $this->render('admin/newGallery.html.twig',[
            'form' => $form,
            'gallery' => $gallery,
            'deleteForm' => $deleteForm,
            'error' => $error
        ]);
    }

    #[Route('/admin/galleries/{id}/delete', name: 'app_admin_delete_gallery', methods: ['POST'])]
     public function deleteGallery(Gallery $gallery, EntityManagerInterface $em): Response
     {
        $images = $gallery->getImages();
        $em->remove($gallery);
        $imgSrc = $gallery->getSrc();
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
                    $galleryImage->setPosition($position + $key);
                    $gallery->addImage($galleryImage);
                    $em->persist($galleryImage);
                }

            try {
                $em->flush();

                foreach ($filesToMove as $key => $file) {
                    $file->move("uploads/images/galleries/",$imgNames[$key]);
                }   

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
}
