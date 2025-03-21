<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\OrderDTO;
use App\DTO\OrderUpdateDTO;
use App\Enum\DeliveryType;
use App\Enum\ItemActionType;
use App\Enum\NotificationType;
use App\Enum\StatusName;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;
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
        ?string $userEmail,
        DeliveryType $deliveryType,
    ) {
        $this->notificationType = $notificationType;
        $this->user = $user;
        $this->address = $address;
        $this->kladrId = $kladrId;
        $this->userPhone = $userPhone;
        $this->userEmail = $userEmail;
        $this->deliveryType = $deliveryType;
        $this->orderStatusHistories = new ArrayCollection();
        $this->orderProducts = new ArrayCollection();
    }

    public static function createFromDTO(
        OrderDTO $orderDTO,
        User $user,
    ): self {
        $order = new self(
            $orderDTO->notificationType,
            $user,
            $orderDTO->address,
            $orderDTO->kladrId,
            $orderDTO->userPhone,
            $orderDTO->userEmail,
            $orderDTO->deliveryType,
        );

        foreach ($orderDTO->orderProductsDTO as $productDTO) {
            $order->addProduct($productDTO->product, $productDTO->amount);
        }

        $order->addStatusHistory(StatusName::REQUIRES_PAYMENT, '', $user);

        return $order;
    }

    public function addStatusHistory(StatusName $status, string $comment, User $user): void
    {
        $statusHistory = new OrderStatusHistory($this, $status, $comment, $user);
        $this->orderStatusHistories->add($statusHistory);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function addProduct(Product $product, int $amount): void
    {
        $orderProduct = new OrderProducts($this, $product, $amount);
        $this->orderProducts->add($orderProduct);
    }

    public function removeProduct(OrderProducts $orderProducts): self
    {
        if ($this->orderProducts->contains($orderProducts)) {
            $this->orderProducts->removeElement($orderProducts);
        }

        return $this;
    }

    public function updateFromDTO(OrderUpdateDTO $updateOrderDTO): void
    {
        foreach ($updateOrderDTO->updateOrderItems as $orderItemDTO) {
            foreach ($this->orderProducts as $existingProduct) {
                if ($existingProduct->getId() === $orderItemDTO->product->getId()) {
                    if ($orderItemDTO->action === ItemActionType::ADD) {
                        $existingProduct->setAmount($existingProduct->getAmount() + $orderItemDTO->quantity);
                    } elseif ($orderItemDTO->action === ItemActionType::REMOVE) {
                        $newQuantity = $existingProduct->getAmount() - $orderItemDTO->quantity;
                        if ($newQuantity < 1) {
                            $this->orderProducts->removeElement($existingProduct);
                        } else {
                            $existingProduct->setAmount($newQuantity);
                        }
                    }
                } else {
                    if ($orderItemDTO->action === ItemActionType::ADD) {
                        $this->addProduct($orderItemDTO->product, $orderItemDTO->quantity);
                    } else {
                        throw new RuntimeException('The product to be deleted was not found in the order. ID: ' . $orderItemDTO->product->getId());
                    }
                }
            }
        }
    }
}
