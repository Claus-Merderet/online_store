<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserAddressesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAddressesRepository::class)]
#[ORM\Table(name: '`user_addresses`')]
class UserAddresses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userAddresses')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?int $kladrId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;
}
