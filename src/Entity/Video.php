<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VideoRepository::class)
 */
class Video
{
    use EntityIdManagementTrait;

    /**
     * @ORM\Column(type="string", length=32)
     */
    #[Assert\Regex(
        pattern: '/^[a-z0-9_-]{7,15}$/i',
        message: "Le nom de la video n'est pas valide. ( entre 7 et 15 lettres, chiffres, - et _ )"
    )]
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trick;

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
