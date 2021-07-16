<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    use EntityIdManagementTrait;

    #[ORM\Column(
        type: "string",
        length: 255,
        unique: true
    )]
    private string $name;

    #[ORM\ManyToOne(
        targetEntity: Trick::class,
        inversedBy: "images"
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Trick $trick;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
