<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\CartDTO;
use App\DTO\CartUpdateDTO;
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

    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function updateCartItemFromDTO(CartUpdateDTO $cartUpdateDTO): void
    {
        foreach ($cartUpdateDTO->cartItemUpdateDTO as $itemUpdateDTO) {
            foreach ($this->cartItems as $existingItem) {
                if ($existingItem->getProduct() === $itemUpdateDTO->product) {
                    if ($itemUpdateDTO->action === ItemActionType::ADD) {
                        $existingItem->setQuantity($existingItem->getQuantity() + $itemUpdateDTO->quantity);
                    } elseif ($itemUpdateDTO->action === ItemActionType::REMOVE) {
                        $newQuantity = $existingItem->getQuantity() - $itemUpdateDTO->quantity;
                        if ($newQuantity < 1) {
                            $this->cartItems->removeElement($existingItem);
                        } else {
                            $existingItem->setQuantity($newQuantity);
                        }
                    }
                } else {
                    if ($itemUpdateDTO->action === ItemActionType::ADD) {
                        $this->addCartItem($itemUpdateDTO->product, $itemUpdateDTO->quantity);
                    } else {
                        throw new RuntimeException('The product to be deleted was not found in the cart. ID: ' . $itemUpdateDTO->product->getId());
                    }
                }
            }
        }
        $this->setUpdatedAt();
    }

    public static function createFromDTO(CartDTO $cartDTO, User $user): self
    {
        $cart = new self($user);

        foreach ($cartDTO->cartItem as $item) {
            $cart->addCartItem($item->product, $item->quantity);
        }

        return $cart;
    }

    public function addCartItem(Product $product, int $quantity): self
    {
        $cartItem = new CartItem($this, $product, $quantity);
        $this->cartItems[] = $cartItem;

        return $this;
    }
}
