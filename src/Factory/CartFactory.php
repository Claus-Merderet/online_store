<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\CartDTO;
use App\Entity\Cart;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

final readonly class CartFactory
{
    public function create(CartDTO $cartDTO, UserInterface $user): Cart
    {
        /* @var User $user */
        Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));
        $cart = new Cart($user);
        foreach ($cartDTO->cartItem as $item) {
            $cart->addCartItem($item->product, $item->quantity);
        }

        return $cart;
    }
}
