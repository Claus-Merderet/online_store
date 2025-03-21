<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\MeasurementsDTO;
use App\DTO\ProductDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RuntimeException;

readonly class ProductService
{
    private const PAGE_SIZE = 10;

    public function __construct(
        private ProductRepository $productRepository,
    ) {
    }

    public function assertProductDoesNotExist(int $id): void
    {
        if ($this->productRepository->find($id) !== null) {
            throw new RuntimeException('A product with this id already exists. ID: ' . $id);
        }
    }

    public function findProductOrFail(int $id): Product
    {
        $product = $this->productRepository->find($id);
        if ($product === null) {
            throw new RuntimeException('A product with this id not found. ID: ' . $id);
        }

        return $product;
    }

    /**
     * @return array{
     *     products: Product[],
     *     page_size: int,
     *     total: int
     * }
     */
    public function getPaginatedProducts(int $page): array
    {
        $queryBuilder = $this->productRepository->createQueryBuilder('p');
        $paginator = new Paginator($queryBuilder);
        $paginator
            ->getQuery()
            ->setFirstResult(self::PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        $products = iterator_to_array($paginator);

        return [
            'products' => $products,
            'page_size' => self::PAGE_SIZE,
            'total' => $paginator->count(),
        ];
    }

    /**
     * @param Product[] $products
     * @return ProductDTO[]
     */
    public function mapProductsToDTO(array $products): array
    {
        return array_map(function (Product $product): ProductDTO {
            $measurements = new MeasurementsDTO(
                $product->getWeight(),
                $product->getHeight(),
                $product->getWidth(),
                $product->getLength(),
            );

            return new ProductDTO(
                $product->getId(),
                $product->getName(),
                $measurements,
                $product->getDescription(),
                $product->getPrice(),
                $product->getTax(),
                $product->getVersion(),
            );
        }, $products);
    }
}
