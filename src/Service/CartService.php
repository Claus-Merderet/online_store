<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CartDTO;
use App\DTO\CartUpdateDTO;
use App\Entity\Cart;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

readonly class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
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
            Assert::notNull($product, 'Product not found. ID: ' . $cartItemUpdateDTO->productId);
            $cart->updateCartItem($product, $cartItemUpdateDTO->quantity, $cartItemUpdateDTO->action);
        }
        $cart->setUpdatedAt();
        $this->entityManager->flush();
    }
}
