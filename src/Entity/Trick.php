<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TrickRepository::class)
 * @UniqueEntity("slug")
 */
class Trick
{
    use EntityIdManagementTrait;

    /**
     * @ORM\Column(type="string", length=128)
     */
    #[Groups(['paginate_trick'])]
    #[Assert\Regex(
        pattern: '/^[\'"_\-)(.,@\s\wÜ-ü]{2,128}$/',
        message: "Le nom du tricks n'est pas valide. ( entre 2 et 128 lettres, chiffres, espaces et @'\"-_/,(). )"
    )]
    #[Assert\NotBlank()]
    private ?string $name = "";

    /**
     * @ORM\Column(type="text")
     */
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
    private ?string $description = "";

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $creationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $editDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tricks")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['paginate_trick'])]
    #[MaxDepth(1)]
    private ?User $author;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="trick", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $messages;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="tricks")
     */
    private Collection $categories;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="trick", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $images;

    /**
     * @ORM\OneToMany(targetEntity=Video::class, mappedBy="trick", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $videos;

    /**
     * @ORM\OneToOne(targetEntity=Image::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Image $hero = null;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    #[Groups(['paginate_trick'])]
    private ?string $slug = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->creationDate = new \DateTime();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getEditDate(): ?\DateTimeInterface
    {
        return $this->editDate;
    }

    public function setEditDate(?\DateTimeInterface $editDate): self
    {
        $this->editDate = $editDate;

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

    /**
     * @return Collection|Message[]
     */
    public function getMessages($maxResults = null, $firstResult = null): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['date' => Criteria::DESC])
            ->setMaxResults($maxResults)
            ->setFirstResult($firstResult)
        ;
        return $this->messages->matching($criteria);
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setTrick($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getTrick() === $this) {
                $message->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addTrick($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeTrick($this);
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setTrick($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getTrick() === $this) {
                $image->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
    }

    public function getHero(): ?Image
    {
        return $this->hero;
    }

    public function setHero(?Image $hero): self
    {
        if ($hero->getTrick() === $this) {
            $this->hero = $hero;
        }

        return $this;
    }

    #[Groups(['paginate_trick'])]
    public function getHeroUrl(): string
    {
        if ($this->getHero()) {
            return "uploads/tricks/" . $this->getHero()->getName();
        }
        if (!$this->getImages()->isEmpty()) {
            return "uploads/tricks/" . $this->getImages()->first()->getName();
        }
        return "imgs/no-image.png";
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function computeSlug(SluggerInterface $slugger, TrickRepository $trickRepository)
    {
        $slug = (string) $slugger->slug((string) $this->getName())->lower();
        $matchedTrick = $trickRepository->findOneBy(['slug' => $slug]);
        if ($matchedTrick && $matchedTrick !== $this && $this->slug !== $slug) {
            $key = 2;
            $matchedTrick = $trickRepository->findOneBy(['slug' => $slug . "_" . $key]);
            while ($matchedTrick && $matchedTrick !== $this) {
                $key++;
                $matchedTrick = $trickRepository->findOneBy(['slug' => $slug . "_" . $key]);
            }
            $slug .= "_" . $key;
        }
        $this->slug = $slug;
    }
}
