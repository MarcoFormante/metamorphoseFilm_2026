<?php

namespace App\Entity;

use App\Repository\ProjectStaffRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectStaffRepository::class)]
class ProjectStaff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $production = null;


    #[ORM\OneToOne(inversedBy: 'projectStaff', cascade: ['persist', 'remove'])]
    private  ?Project $project = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artists = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $montage = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $cadrage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $droniste = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ph_plateau = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $decorateurs = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $moreStaffFields;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduction(): ?string
    {
        return $this->production;
    }

    public function setProduction(string $production): static
    {
        $this->production = $production;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

   

    public function getArtists(): ?string
    {
        return $this->artists;
    }

    public function setArtists(?string $artists): static
    {
        $this->artists = $artists;

        return $this;
    }

    public function getMontage(): ?string
    {
        return $this->montage;
    }

    public function setMontage(string $montage): static
    {
        $this->montage = $montage;

        return $this;
    }

    public function getCadrage(): ?string
    {
        return $this->cadrage;
    }

    public function setCadrage(string $cadrage): static
    {
        $this->cadrage = $cadrage;

        return $this;
    }

    public function getDroniste(): ?string
    {
        return $this->droniste;
    }

    public function setDroniste(?string $droniste): static
    {
        $this->droniste = $droniste;

        return $this;
    }

    public function getPhPlateau(): ?string
    {
        return $this->ph_plateau;
    }

    public function setPhPlateau(?string $ph_plateau): static
    {
        $this->ph_plateau = $ph_plateau;

        return $this;
    }

    public function getDecorateurs(): ?string
    {
        return $this->decorateurs;
    }

    public function setDecorateurs(?string $decorateurs): static
    {
        $this->decorateurs = $decorateurs;

        return $this;
    }

    public function getMoreStaffFields(): ?string
    {
        return $this->moreStaffFields;
    }

    public function setMoreStaffFields(?string $moreStaffFields): static
    {
        $this->moreStaffFields = $moreStaffFields;

        return $this;
    }
}
