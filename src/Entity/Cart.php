<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ItemActionType;
use App\Repository\CartRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'cart')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * @var Collection<int,CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['cart'])]
    private Collection $cartItems;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Groups(['cart'])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    #[Groups(['cart'])]
    private ?DateTimeInterface $updatedAt = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->cartItems = new ArrayCollection();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function updateCartItem(Product $product, int $quantity, ItemActionType $actionType): void
    {
        foreach ($this->cartItems as $existingItem) {
            if ($existingItem->getProduct() === $product) {
                if ($actionType === ItemActionType::ADD) {
                    $existingItem->setQuantity($existingItem->getQuantity() + $quantity);

                    return;
                } elseif ($actionType === ItemActionType::REMOVE) {
                    $newQuantity = $existingItem->getQuantity() - $quantity;
                    if ($newQuantity < 1) {
                        $this->cartItems->removeElement($existingItem);
                    } else {
                        $existingItem->setQuantity($newQuantity);
                    }

                    return;
                }
            }
        }
        if ($actionType === ItemActionType::ADD) {
            $this->addCartItem($product, $quantity);
        } else {
            throw new RuntimeException('The product to be deleted was not found in the cart. ID: ' . $product->getId());
        }
    }

    public function addCartItem(Product $product, int $quantity): self
    {
        $cartItem = new CartItem($this, $product, $quantity);
        $this->cartItems[] = $cartItem;

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->contains($cartItem)) {
            $this->cartItems->removeElement($cartItem);
        }

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }
}
