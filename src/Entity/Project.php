<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table('project')]
#[ORM\Index(name:'idx_updated_at',columns:['updated_at'])]

class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    
    private ?int $id = null;

    #[ORM\Column(length: 255,unique:true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $thumb = null;

    #[ORM\Column(length: 255)]
    private ?string $youtube_video = null;

    #[ORM\Column(length: 255)]
    private ?string $background_video = null;

    #[ORM\Column(length: 255)]
    private ?string $made_by = null;



    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?ProjectStaff $projectStaff = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $collab_with = null;

    /**
     * @var Collection<int, ProjectImages>
     */
    #[ORM\OneToMany(targetEntity: ProjectImages::class, mappedBy: 'project_id', orphanRemoval: true)]
    private Collection $projectImages;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255)]
    private ?string $abrName = null;

    #[ORM\Column (type: 'integer', nullable: true)]
    private ?int $orderIndex = null;

    #[ORM\Column(name:'updated_at')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255,unique:true)]
    private ?string $slug = null;


    public function __construct()
    {
        $this->projectImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getThumb(): ?string
    {
        return $this->thumb;
    }

    public function setThumb(string $thumb): static
    {
        $this->thumb = $thumb;

        return $this;
    }

    public function getYoutubeVideo(): ?string
    {
        return $this->youtube_video;
    }

    public function setYoutubeVideo(string $youtube_video): static
    {
        $this->youtube_video = $youtube_video;

        return $this;
    }

    public function getBackgroundVideo(): ?string
    {
        return $this->background_video;
    }

    public function setBackgroundVideo(string $background_video): static
    {
        $this->background_video = $background_video;

        return $this;
    }

    public function getMadeBy(): ?string
    {
        return $this->made_by;
    }

    public function setMadeBy(string $made_by): static
    {
        $this->made_by = $made_by;

        return $this;
    }

    public function getProjectStaff(): ?ProjectStaff
    {
        return $this->projectStaff;
    }

    public function setProjectStaff(?ProjectStaff $projectStaff): static
    {
        // unset the owning side of the relation if necessary
        if ($projectStaff === null && $this->projectStaff !== null) {
            $this->projectStaff->setProject(null);
        }

        // set the owning side of the relation if necessary
        if ($projectStaff !== null && $projectStaff->getProject() !== $this) {
            $projectStaff->setProject($this);
        }

        $this->projectStaff = $projectStaff;

        return $this;
    }

    public function getCollabWith(): ?string
    {
        return $this->collab_with;
    }

    public function setCollabWith(?string $collab_with): static
    {
        $this->collab_with = $collab_with;

        return $this;
    }

    /**
     * @return Collection<int, ProjectImages>
     */
    public function getProjectImages(): Collection
    {
        return $this->projectImages;
    }

    public function addProjectImage(ProjectImages $projectImage): static
    {
        if (!$this->projectImages->contains($projectImage)) {
            $this->projectImages->add($projectImage);
            $projectImage->setProjectId($this);
        }

        return $this;
    }

    public function removeProjectImage(ProjectImages $projectImage): static
    {
        if ($this->projectImages->removeElement($projectImage)) {
            // set the owning side to null (unless already changed)
            if ($projectImage->getProjectId() === $this) {
                $projectImage->setProjectId(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAbrName(): ?string
    {
        return $this->abrName;
    }

    public function setAbrName(string $abrName): static
    {
        $this->abrName = $abrName;

        return $this;
    }


    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): static
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

   

}
