<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\CartItemDTO;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class CartFactory
{
    public function create(CartItemDTO $cartItemDTO, Product $product, UserInterface $user): Cart
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Expected instance of App\Entity\User');
        }
        $cart = new Cart($user);
        $cartItem = new CartItem(
            $product,
            $cartItemDTO->quantity,
        );
        $cart->addCartItem($cartItem);

        return $cart;
    }
}
