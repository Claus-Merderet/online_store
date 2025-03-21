<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\RegisterUserDTO;
use App\Interface\AuthUserInterface;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[UniqueEntity(fields: ['email'], message: 'This email is already in use.')]
#[UniqueEntity(fields: ['phone'], message: 'This phone number is already in use.')]
class User implements AuthUserInterface
{
    #[Assert\Uuid(message: 'Invalid promoId. It must be a valid UUID.')]
    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    #[Groups(['user:index'])]
    public ?string $promoId = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:index','user:index'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:index','user:index'])]
    private Role $role;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Assert\Email]
    #[Groups(['user:index'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $birthday = null;

    #[ORM\Column(length: 15, unique: true, nullable: true)] // TODO: сделать уникальным
    #[Groups(['user:index'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:index','user:index'])]
    private ?string $name = null;

    #[ORM\Column(length: 64)]
    private string $password;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, UserAddresses>
     */
    #[ORM\OneToMany(targetEntity: UserAddresses::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userAddresses;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $orders;

    #[ORM\OneToOne(targetEntity: Cart::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Cart $cart = null;

    public function __construct(
        ?string $email,
        ?string $phone,
        ?string $promoId,
        Role $role,
        string $password,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->email = $email ?? '';
        $this->phone = $phone ?? '';
        $this->promoId = $promoId ?? '';
        $this->role = $role;
        $this->password = $passwordHasher->hashPassword($this, $password);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getRoles(): array
    {
        return [$this->role->getRoleName()];
    }

    public function eraseCredentials(): void
    {
        //Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return !empty($this->email) ? $this->email : $this->phone;
    }

    public static function createFromDTO(
        RegisterUserDTO $registerUserDTO,
        Role $role,
        UserPasswordHasherInterface $passwordHasher,
    ): self {
        return new self(
            $registerUserDTO->email,
            $registerUserDTO->phone,
            $registerUserDTO->promoId,
            $role,
            $registerUserDTO->password,
            $passwordHasher,
        );
    }
}
