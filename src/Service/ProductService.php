<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\MeasurementsDTO;
use App\DTO\ProductDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ProductService
{
    private const PAGE_SIZE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ProductRepository $productRepository
    ) {
    }

    public function validateDTO(ProductDTO $dto): ?JsonResponse
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->createValidationErrorResponse($errors);
        }

        return null;
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

        return new JsonResponse(['error' => 'Validation failed', 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function createProduct(ProductDTO $productDTO): Product
    {
        $product = new Product($productDTO);
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function updateProduct(Product $product, ProductDTO $productDTO): Product
    {
        $product->syncWithDTO($productDTO);
        $this->entityManager->flush();

        return $product;
    }

    public function productExists(int $id): bool
    {
        return (bool)$this->entityManager->getRepository(Product::class)->find($id);
    }

    public function findProduct(int $id): ?Product
    {
        return $this->entityManager->getRepository(Product::class)->find($id);
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
                $product->getLength()
            );

            return new ProductDTO(
                $product->getId(),
                $product->getName(),
                $measurements,
                $product->getDescription(),
                $product->getPrice(),
                $product->getTax(),
                $product->getVersion()
            );
        }, $products);
    }
}
