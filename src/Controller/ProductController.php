<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ProductDTO;
use App\Entity\Product;
use App\Enum\RoleName;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Product')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    #[OA\Get(summary: 'Return a product list')]
    public function index(#[MapQueryParameter] int $page = 1): JsonResponse
    {
        $paginationData = $this->productService->getPaginatedProducts($page);
        $productsDTO = $this->productService->mapProductsToDTO($paginationData['products']);
        $responseData = [
            'data' => $productsDTO,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => ceil($paginationData['total'] / $paginationData['page_size']),
                'pageSize' => $paginationData['page_size'],
                'totalItems' => $paginationData['total'],
            ],
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    #[OA\Post(
        description: 'Creates a new product. Only accessible to users with the ADMIN role.',
        summary: 'Create a new product',
    )]
    #[IsGranted(RoleName::ADMIN->value, message: 'Only admins can create products.')]
    #[Security(name: 'Bearer')]
    public function create(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
    )] ProductDTO $productDTO): JsonResponse
    {
        try {
            $this->productService->assertProductDoesNotExist($productDTO->id);
            $product = $this->productService->createProduct($productDTO);

            return new JsonResponse(
                ['message' => 'Product created successfully', 'id' => $product->getId()],
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to create a product: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/api/products', name: 'product_update', methods: ['PUT'])]
    #[OA\Put(summary: 'Update a product')]
    #[IsGranted(RoleName::ADMIN->value, message: 'Only admins can create products.')]
    #[Security(name: 'Bearer')]
    public function update(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
    )] ProductDTO $productDTO): JsonResponse
    {
        try {
            $product = $this->productService->findProductOrFail($productDTO->id);
            $this->productService->updateProduct($product, $productDTO);

            return new JsonResponse(
                ['message' => 'Product updated successfully'],
                Response::HTTP_ACCEPTED,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to create a product: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/api/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete a product')]
    #[IsGranted(RoleName::ADMIN->value, message: 'Only admins can delete products.')]
    #[Security(name: 'Bearer')]
    public function delete(#[MapEntity(id: 'id')] Product $product): JsonResponse
    {
        try {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            return new JsonResponse(
                ['message' => 'Product removed successfully. ID: ' . $product->getId()],
                Response::HTTP_ACCEPTED,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to delete a product: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
