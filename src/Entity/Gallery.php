<?php

namespace App\Entity;

use App\Repository\GalleryRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $src = null;

    #[ORM\Column]
    private ?int $position = null;

    /**
     * @var Collection<int, GalleryImages>
     */
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: GalleryImages::class, cascade: ['persist','remove'])]
    private Collection $images;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function setSrc(string $src): static
    {
        $this->src = $src;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, GalleryImages>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(GalleryImages $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setGallery($this);
        }

        return $this;
    }

    public function removeImage(GalleryImages $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getGallery() === $this) {
                $image->setGallery(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): static
    {
        $this->updatedAt = new DateTimeImmutable('now');

        return $this;
    }
}
