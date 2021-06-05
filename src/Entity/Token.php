<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TokenRepository::class)
 */
class Token
{
    use EntityIdManagementTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $expiresAt;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="token", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    public function __construct(User $user, int $lifetime = 3)
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT' . $lifetime . 'H'));
        $this->expiresAt = $date;
        $this->setUser($user);
        $this->value = md5(uniqid(rand(), true));
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        $user->setToken($this);

        return $this;
    }

    public function isValid(): bool
    {
        return $this->expiresAt >= new \DateTime();
    }
}
