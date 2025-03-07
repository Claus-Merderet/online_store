<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\CartDTO;
use App\DTO\CartItemDTO;
use App\Entity\Product;
use App\Entity\User;
use App\Factory\CartFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CartFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly CartFactory $cartFactory)
    {
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user = $this->getReference(UserFixtures::USER_REFERENCE . $i, User::class);
            $cartDTO = $this->createCartDTO($i);
            $cart = $this->cartFactory->create($cartDTO, $user);
            $manager->persist($cart);
        }

        $manager->flush();
    }

    private function createCartDTO(int $currentIndexUser): CartDTO
    {
        $cartItemsDTO = [];
        for ($i = 0; $i <= $currentIndexUser; $i++) {
            $product = $this->getReference(ProductFixtures::PRODUCT_REFERENCE . $i, Product::class);
            $cartItemsDTO[] = new CartItemDTO($product->getId(), rand(1, 10), $product);
        }

        return new CartDTO($cartItemsDTO);
    }
}
