<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

trait EntityIdManagementTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"paginate_user"})
     */
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}