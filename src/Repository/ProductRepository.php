<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function findById(string $id): Product|null
    {
        return $this->entityManager->getRepository(Product::class)->find($id);
    }

    /**
     * @return null|array{id: int, name: string, description: null|string, price: int, tax: int, version: int, weight: int, height: int, width: int, length: int}
     */
    public function findByIdAsArray(string $id): array|null
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if ($product === null) {
            return null;
        }

        return $this->entityManager->getUnitOfWork()->getOriginalEntityData($product);
    }

    public function deleteById(string $id): Product|null
    {
        $product = $this->findById($id);

        if ($product) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        }

        return $product;
    }
}
