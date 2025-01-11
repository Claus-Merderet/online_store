<?php declare(strict_types=1);

namespace App\Entity;

use App\Enum\DeliveryType;
use App\Enum\NotificationType;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`orders`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: NotificationType::class)]
    private NotificationType $notificationType;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    private ?int $kladrId = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $userPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userEmail = null;

    #[ORM\Column(enumType: DeliveryType::class)]
    private DeliveryType $deliveryType;

    /**
     * @var Collection<int, OrderStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: OrderStatusHistory::class, mappedBy: 'orderId', orphanRemoval: true)]
    private Collection $orderStatusHistories;

    /**
     * @var Collection<int, OrderProducts>
     */
    #[ORM\OneToMany(targetEntity: OrderProducts::class, mappedBy: 'orderId', orphanRemoval: true)]
    private Collection $orderProducts;

    public function __construct()
    {
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


}
