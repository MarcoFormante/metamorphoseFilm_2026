<?php

namespace App\Tests\Controller;

use App\Entity\Gallery;
use App\Entity\GalleryImages;
use App\Entity\ServiceVideo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?\Symfony\Bundle\FrameworkBundle\KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        // tests run in 'test' environment; controller uses a lightweight copy strategy in tests
    }

    private function createAdminUser(): User
    {
        $user = new User();
        $user->setUsername('admin_test_' . random_int(100000, 999999));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword('test');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createServiceVideo(string $category): ServiceVideo
    {
        $video = new ServiceVideo();
        $video->setVideoLink('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $video->setTitle('Test video');
        $video->setCategory($category);
        $video->setIsShort(false);
        $video->setPosition(1);
        $video->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->entityManager->persist($video);
        $this->entityManager->flush();

        return $video;
    }

    private function createGallery(?string $name = null): Gallery
    {
        $gallery = new Gallery();
        $gallery->setName($name ?? 'gallery_test_' . random_int(100000, 999999));
        $gallery->setSrc('gallery_test_image.webp');
        $gallery->setPosition(1);
        $gallery->setUpdatedAt();

        $image = new GalleryImages();
        $image->setSrc('gallery_test_image_' . random_int(100000, 999999) . '.webp');
        $image->setPosition(1);
        $image->setGallery($gallery);

        $gallery->addImage($image);

        $this->entityManager->persist($gallery);
        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $gallery;
    }

    public function testAdminHomeRequiresLogin(): void
    {
        $client = $this->client;
        $client->request('GET', '/admin/home');

        $this->assertResponseRedirects('/login', 302);
    }

    public function testAdminHomeAccessibleForAdminUser(): void
    {
        $client = $this->client;
        $client->loginUser($this->createAdminUser());
        $client->request('GET', '/admin/home');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase('admin', $client->getResponse()->getContent());
    }

    public function testAdminGalleriesAccessibleForAdminUser(): void
    {
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $client->request('GET', '/admin/galleries');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase('Galerie', $client->getResponse()->getContent());
    }

    public function testAdminServicesAccessibleForAdminUser(): void
    {
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $client->request('GET', '/admin/services');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase('services', $client->getResponse()->getContent());
    }

    public function testAdminDevAccessibleForAdminUser(): void
    {
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $client->request('GET', '/admin/dev');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase('cache', $client->getResponse()->getContent());
    }

    public function testAdminSingleServicePageAccessibleForAdminUser(): void
    {
        $category = 'services_test_' . random_int(100000, 999999);
        $video = $this->createServiceVideo($category);

        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $client->request('GET', '/admin/services/' . $category);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase($video->getCategory(), $client->getResponse()->getContent());
    }

    public function testAdminGalleryPageAccessibleForAdminUser(): void
    {
        $gallery = $this->createGallery();

        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $client->request('GET', '/admin/galleries/' . $gallery->getName());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase($gallery->getName(), $client->getResponse()->getContent());
    }

    public function testCreateNewGalleryWithImageUploads(): void
    {
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $crawler = $client->request('GET', '/admin/newGallery');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="create_gallery"]')->form();

        $name = 'gallery_test_upload_' . random_int(100000, 999999);
        $tmp = sys_get_temp_dir() . '/gtest_' . random_int(100000, 999999) . '.png';
        file_put_contents($tmp, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAn8B9Qk6bs0AAAAASUVORK5CYII='));
        $file = new UploadedFile($tmp, 'test.png', 'image/png', null, true);

        $client->submit($form, ['create_gallery[name]' => $name], ['create_gallery[image]' => $file]);

        $this->assertResponseRedirects();

        // cleanup: remove created gallery and uploaded file
        $em = $this->entityManager;
        $repo = $em->getRepository(\App\Entity\Gallery::class);
        $g = $repo->findOneBy(['name' => $name]);
        if ($g) {
            $img = $g->getSrc();
            $path = 'uploads/images/gallery/' . $img;
            $em->remove($g);
            $em->flush();
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        @unlink($tmp);
    }

    public function testEditGalleryReplaceImage(): void
    {
        $gallery = $this->createGallery();
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $crawler = $client->request('GET', '/admin/galleries/' . $gallery->getName() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="create_gallery"]')->form();

        $tmp = sys_get_temp_dir() . '/gedit_' . random_int(100000, 999999) . '.png';
        file_put_contents($tmp, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAn8B9Qk6bs0AAAAASUVORK5CYII='));
        $file = new UploadedFile($tmp, 'edit.png', 'image/png', null, true);

        $client->submit($form, ['create_gallery[name]' => $gallery->getName(), 'create_gallery[lastImage]' => $gallery->getSrc()], ['create_gallery[image]' => $file]);

        $this->assertResponseRedirects();

        // cleanup
        $em = $this->entityManager;
        $g = $em->getRepository(\App\Entity\Gallery::class)->find($gallery->getId());
        if ($g) {
            $img = $g->getSrc();
            $path = 'uploads/images/gallery/' . $img;
            $em->remove($g);
            $em->flush();
            if (file_exists($path)) {@unlink($path);} 
        }
        @unlink($tmp);
    }

    public function testAddImagesToGallery(): void
    {
        $gallery = $this->createGallery();
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        $crawler = $client->request('GET', '/admin/gallery/' . $gallery->getName() . '/add-images');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="add_gallery_images"]')->form();

        $tmp1 = sys_get_temp_dir() . '/gadd1_' . random_int(100000, 999999) . '.png';
        $tmp2 = sys_get_temp_dir() . '/gadd2_' . random_int(100000, 999999) . '.png';
        file_put_contents($tmp1, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAn8B9Qk6bs0AAAAASUVORK5CYII='));
        file_put_contents($tmp2, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAn8B9Qk6bs0AAAAASUVORK5CYII='));
        $file1 = new UploadedFile($tmp1, 'one.png', 'image/png', null, true);
        $file2 = new UploadedFile($tmp2, 'two.png', 'image/png', null, true);

        $client->submit($form, [], ['add_gallery_images[files][0]' => $file1, 'add_gallery_images[files][1]' => $file2]);

        $this->assertResponseRedirects();

        // cleanup created gallery images and gallery
        $em = $this->entityManager;
        $g = $em->getRepository(\App\Entity\Gallery::class)->find($gallery->getId());
        if ($g) {
            foreach ($g->getImages() as $img) {
                $p = 'uploads/images/galleries/' . $img->getSrc();
                if (file_exists($p)) {@unlink($p);} 
                $em->remove($img);
            }
            $em->remove($g);
            $em->flush();
        }

        @unlink($tmp1);
        @unlink($tmp2);
    }

    public function testDeleteGalleryEndpointRemovesGallery(): void
    {
        $gallery = $this->createGallery();
        $client = $this->client;
        $client->loginUser($this->createAdminUser());

        // refresh repository to retrieve id reliably
        $em = $this->entityManager;
        $fresh = $em->getRepository(\App\Entity\Gallery::class)->findOneBy(['name' => $gallery->getName()]);
        $this->assertNotNull($fresh, 'Created gallery not found before delete');
        $client->request('POST', '/admin/galleries/' . $fresh->getId() . '/delete');
        $this->assertResponseRedirects();

        $em = $this->entityManager;
        $g = $em->getRepository(\App\Entity\Gallery::class)->findOneBy(['id' => $fresh->getId()]);
        $this->assertNull($g);
    }
}
