<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: '`roles`')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['order:index'])]
    private string $roleName;

    public function __construct(string $roleName)
    {
        $this->roleName = $roleName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }
}
