<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\OrderDTO;
use App\DTO\OrderProductDTO;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\DeliveryType;
use App\Enum\NotificationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct()
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
            $orderDTO = $this->createOrderDTO($i, $user->getPhone(), $user->getEmail());
            $order = Order::createFromDTO($orderDTO, $user);
            $manager->persist($order);
        }

        $manager->flush();
    }

    private function createOrderDTO(int $currentIndexUser, string $phone, string $email): OrderDTO
    {
        $orderProductsDTO = [];
        for ($i = 0; $i <= $currentIndexUser; $i++) {
            $product = $this->getReference(ProductFixtures::PRODUCT_REFERENCE . $i, Product::class);
            $orderProductsDTO[] = new OrderProductDTO(
                $product->getId(),
                $product->getPrice(),
                rand(1, 10),
                $product->getTax(),
                $product,
            );
        }

        return new OrderDTO(
            NotificationType::EMAIL,
            $orderProductsDTO,
            '',
            $currentIndexUser,
            $phone,
            $email,
            DeliveryType::SELF_DELIVERY,
        );
    }
}
