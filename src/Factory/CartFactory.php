<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\CartDto;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class CartFactory
{
    public function create(CartDTO $cartDTO, UserInterface $user): Cart
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Expected instance of App\Entity\User');
        }
        $cart = new Cart($user);
        foreach ($cartDTO->cartItems as $item) {
            $cartItem = new CartItem(
                $item->product,
                $item->quantity,
            );
            $cart->addCartItem($cartItem);
        }

        return $cart;
    }
}
