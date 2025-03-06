<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\MeasurementsDTO;
use App\DTO\ProductDTO;
use App\Entity\Product;
use App\Factory\ProductFactory;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

readonly class ProductService
{
    private const PAGE_SIZE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private ProductFactory $productFactory,
    ) {
    }

    public function assertProductDoesNotExist(int $id): void
    {
        $product = $this->productRepository->find($id);
        if ($product !== null) {
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

    private function createValidationErrorResponse(ConstraintViolationListInterface $violations): JsonResponse
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new JsonResponse(
            ['error' => 'Validation failed', 'errors' => $errors],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    public function createProduct(ProductDTO $productDTO): Product
    {
        $product = $this->productFactory->create($productDTO);
        $this->productRepository->save($product);

        return $product;
    }

    public function updateProduct(Product $product, ProductDTO $productDTO): Product
    {
        $product->syncWithDTO($productDTO);
        $this->entityManager->flush();

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
