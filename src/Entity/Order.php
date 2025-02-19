<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\DeliveryType;
use App\Enum\NotificationType;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`orders`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:index'])]
    private ?int $id = null;

    #[ORM\Column(enumType: NotificationType::class)]
    #[Groups(['order:index'])]
    private NotificationType $notificationType;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:index'])]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['order:index'])]
    private ?int $kladrId = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(['order:index'])]
    private ?string $userPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:index'])]
    private ?string $userEmail = null;

    #[ORM\Column(enumType: DeliveryType::class)]
    #[Groups(['order:index'])]
    private DeliveryType $deliveryType;

    /**
     * @var Collection<int, OrderStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: OrderStatusHistory::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['order:index'])]
    private Collection $orderStatusHistories;

    /**
     * @var Collection<int, OrderProducts>
     */
    #[ORM\OneToMany(targetEntity: OrderProducts::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['order:index'])]
    private Collection $orderProducts;

    public function __construct(
        NotificationType $notificationType,
        User $user,
        ?string $address,
        ?int $kladrId,
        ?string $userPhone,
        DeliveryType $deliveryType,
    ) {
        $this->notificationType = $notificationType;
        $this->user = $user;
        $this->address = $address;
        $this->kladrId = $kladrId;
        $this->userPhone = $userPhone;
        $this->deliveryType = $deliveryType;
        $this->orderStatusHistories = new ArrayCollection();
        $this->orderProducts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNotificationType(): ?NotificationType
    {
        return $this->notificationType;
    }

    public function setNotificationType(NotificationType $notificationType): static
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getKladrId(): ?int
    {
        return $this->kladrId;
    }

    public function setKladrId(?int $kladrId): static
    {
        $this->kladrId = $kladrId;

        return $this;
    }

    public function getUserPhone(): ?string
    {
        return $this->userPhone;
    }

    public function setUserPhone(?string $userPhone): static
    {
        $this->userPhone = $userPhone;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(?string $userEmail): static
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    public function getDeliveryType(): ?DeliveryType
    {
        return $this->deliveryType;
    }

    public function setDeliveryType(DeliveryType $deliveryType): static
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }

    /**
     * @return Collection<int, OrderStatusHistory>
     */
    public function getOrderStatusHistories(): Collection
    {
        return $this->orderStatusHistories;
    }

    /**
     * @return Collection<int, OrderProducts>
     */
    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function addProduct(OrderProducts $orderProducts): self
    {
        if (!$this->orderProducts->contains($orderProducts)) {
            $this->orderProducts[] = $orderProducts;
        }

        return $this;
    }

    public function removeProduct(OrderProducts $orderProducts): self
    {
        if ($this->orderProducts->contains($orderProducts)) {
            $this->orderProducts->removeElement($orderProducts);
        }

        return $this;
    }

    public function addStatusHistory(OrderStatusHistory $orderStatusHistory): self
    {
        if (!$this->orderStatusHistories->contains($orderStatusHistory)) {
            $this->orderStatusHistories[] = $orderStatusHistory;
        }

        return $this;
    }
}
