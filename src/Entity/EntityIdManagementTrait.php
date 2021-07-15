<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

trait EntityIdManagementTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['paginate_user', 'paginate_trick', 'paginate_message'])]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}