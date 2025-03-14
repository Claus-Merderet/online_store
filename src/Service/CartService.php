<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CartDTO;
use App\DTO\CartUpdateDTO;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Enum\ItemActionType;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private CartItemRepository $cartItemRepository,
        private CartRepository $cartRepository,
    ) {
    }

    public function fillProductsDTO(CartDTO $cartDTO): void
    {
        foreach ($cartDTO->cartItem as $item) {
            $product = $this->productRepository->find($item->productId);
            if ($product === null) {
                throw new RuntimeException('Product not found. ID:' . $item->productId);
            }
            $item->product = $product;
        }
    }

    public function checkExistCart(UserInterface $user): void
    {
        if ($this->cartRepository->findByUser($user) !== null) {
            throw new RuntimeException('Cart already exists');
        }
    }

    public function checkPermissions(bool $isCartCreator, bool $isAdmin): void
    {
        if (!$isCartCreator && !$isAdmin) {
            throw new AccessDeniedException('You do not have sufficient permissions to perform this action.');
        }
    }

    /**
     * @throws Exception
     */
    public function updateCart(Cart $cart, CartUpdateDTO $cartUpdateDTO): void
    {
        foreach ($cartUpdateDTO->cartItemUpdateDTO as $cartItemUpdateDTO) {
            $product = $this->productRepository->find($cartItemUpdateDTO->productId);
            if ($product === null) {
                throw new RuntimeException('Product not found. ID: ' . $cartItemUpdateDTO->productId);
            }

            $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'product' => $product]);

            if ($cartItemUpdateDTO->action === ItemActionType::ADD) {
                $this->addOrUpdateCartItem($cart, $product, $cartItemUpdateDTO->quantity, $cartItem);
            } elseif ($cartItemUpdateDTO->action === ItemActionType::REMOVE) {
                $this->removeOrUpdateCartItem($cart, $product, $cartItemUpdateDTO->quantity, $cartItem);
            }
        }
        $cart->setUpdatedAt();
        $this->entityManager->flush();
    }

    private function addOrUpdateCartItem(Cart $cart, Product $product, int $quantity, ?CartItem $cartItem): void
    {
        if ($cartItem instanceof CartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem($cart, $product, $quantity);
        }

        $this->entityManager->persist($cartItem);
    }

    private function removeOrUpdateCartItem(Cart $cart, Product $product, int $quantity, ?CartItem $cartItem): void
    {
        if (!$cartItem instanceof CartItem) {
            throw new RuntimeException('The product to be deleted was not found in the cart. ID: ' . $product->getId());
        }

        $newAmount = $cartItem->getQuantity() - $quantity;

        if ($newAmount < 1) {
            $this->entityManager->remove($cartItem);
            $cart->removeCartItem($cartItem);
        } else {
            $cartItem->setQuantity($newAmount);
            $this->entityManager->persist($cartItem);
        }
    }
}
