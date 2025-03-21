<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\ProductDTO;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class ProductFixtures extends Fixture
{
    private const PRODUCTS_DATA = [
        [
            'id' => 1,
            'name' => 'Phone',
            'measurements' => [
                'weight' => 100,
                'height' => 40,
                'width' => 2,
                'length' => 2,
            ],
            'description' => '',
            'cost' => 15000,
            'tax' => 10,
            'version' => 1,
        ],[
            'id' => 2,
            'name' => 'Case',
            'measurements' => [
                'weight' => 10,
                'height' => 10,
                'width' => 2,
                'length' => 2,
            ],
            'description' => '',
            'cost' => 500,
            'tax' => 10,
            'version' => 1,
        ],[
            'id' => 3,
            'name' => 'Watch',
            'measurements' => [
                'weight' => 100,
                'height' => 2,
                'width' => 15,
                'length' => 15,
            ],
            'description' => 'some description',
            'cost' => 4500,
            'tax' => 10,
            'version' => 1,
        ],
    ];

    public const PRODUCT_REFERENCE = 'product-';

    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::PRODUCTS_DATA as $index => $productData) {
            $productDTO = $this->serializer->deserialize(
                json_encode($productData),
                ProductDTO::class,
                'json',
            );
            $product = Product::createFromDTO($productDTO);
            $manager->persist($product);
            $this->addReference(self::PRODUCT_REFERENCE . $index, $product);
        }

        $manager->flush();
    }
}
