<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    use EntityIdManagementTrait;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"paginate_message"})
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(['paginate_message'])]
    #[Assert\Length(
        min: 2,
        max: 2500,
        minMessage: "Minimum 2 caractères.",
        maxMessage: "Maximum 2500 caractères."
    )]
    #[Assert\Regex(
        pattern: '/[<>&]/',
        message: "Les caractères \"<, > et &\" sont interdits.",
        match: false
    )]
    #[Assert\NotBlank()]
    private string $content;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['paginate_message'])]
    #[MaxDepth(1)]
    private ?User $author;

    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Trick $trick;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
