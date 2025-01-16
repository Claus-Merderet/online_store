<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\RegisterUserDTO;
use App\Interface\AuthUserInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[UniqueEntity(fields: ['email'], message: 'This email is already in use.')]
#[UniqueEntity(fields: ['phone'], message: 'This phone number is already in use.')]
class User implements AuthUserInterface
{
    #[Assert\Uuid(message: 'Invalid promoId. It must be a valid UUID.')]
    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    public ?string $promoId = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Role $role;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\Column(length: 15, unique: true, nullable: true)] // TODO: сделать уникальным
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 64)]
    private string $password;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, UserAddresses>
     */
    #[ORM\OneToMany(targetEntity: UserAddresses::class, mappedBy: 'userId', orphanRemoval: true)]
    private Collection $userAddresses;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'userId', orphanRemoval: true)]
    private Collection $orders;

    public function __construct(RegisterUserDTO $registerUserDTO, Role $role)
    {
        $this->email = $registerUserDTO->email ?? '';
        $this->phone = $registerUserDTO->phone ?? '';
        $this->promoId = $registerUserDTO->promoId ?? '';
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password, UserPasswordHasherInterface $passwordHasher): void
    {
        $this->password = $passwordHasher->hashPassword($this, $password);
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getRoles(): array
    {
        return [$this->role->getRoleName()];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return !empty($this->email) ? $this->email : $this->phone;
    }

    /**
     * @param  Collection<int, UserAddresses> $userAddresses
     */
    public function setUserAddresses(Collection $userAddresses): void
    {
        $this->userAddresses = $userAddresses;
    }

    /**
     * @param  Collection<int, Order> $orders
     */
    public function setOrders(Collection $orders): void
    {
        $this->orders = $orders;
    }
}
