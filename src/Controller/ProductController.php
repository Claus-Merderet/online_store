<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ProductDTO;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
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
    public function __construct(private readonly ProductService $productService, private ProductRepository $productRepository)
    {
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
    #[IsGranted('ROLE_ADMIN', message: 'Only admins can create products.')]
    #[Security(name: 'Bearer')]
    public function create(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
    )] ProductDTO $productDTO): JsonResponse
    {
        $validationErrors = $this->productService->validateDTO($productDTO);
        if ($validationErrors !== null) {
            return $validationErrors;
        }

        if ($this->productRepository->find($productDTO->id) !== null) {
            return new JsonResponse(
                ['error' => 'Product with this ID already exists: ' . $productDTO->id],
                Response::HTTP_BAD_REQUEST,
            );
        }
        $product = $this->productService->createProduct($productDTO);

        return new JsonResponse(
            ['message' => 'Product created successfully', 'id' => $product->getId()],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/api/products', name: 'product_update', methods: ['PUT'])] // TODO: заменить название ролей на константы
    #[OA\Put(summary: 'Update a product')]
    #[IsGranted('ROLE_ADMIN', message: 'Only admins can create products.')]
    #[Security(name: 'Bearer')]
    public function update(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
    )] ProductDTO $productDTO): JsonResponse
    {
        $validationErrors = $this->productService->validateDTO($productDTO);
        if ($validationErrors !== null) {
            return $validationErrors;
        }
        $product = $this->productRepository->find($productDTO->id);

        if ($product === null) {
            return new JsonResponse(
                ['error' => 'Product not found', 'id' => $productDTO->id],
                Response::HTTP_BAD_REQUEST,
            );
        }
        $this->productService->updateProduct($product, $productDTO);

        return new JsonResponse(
            ['message' => 'Product updated successfully'],
            Response::HTTP_ACCEPTED,
        );
    }

    #[Route('/api/products', name: 'product_delete', methods: ['DELETE'])]
    #[OA\Put(summary: 'Delete a product')]
    #[IsGranted('ROLE_ADMIN', message: 'Only admins can delete products.')]
    #[Security(name: 'Bearer')]
    public function delete(#[MapQueryParameter] int $productId = 1): JsonResponse
    {
        $product = $this->productRepository->deleteById($productId);

        if ($product === null) {
            return new JsonResponse(
                ['error' => 'Product not found', 'id' => $productId],
                Response::HTTP_BAD_REQUEST,
            );
        }

        return new JsonResponse(
            ['message' => 'Product removed successfully. ID: ' . $productId],
            Response::HTTP_ACCEPTED,
        );
    }
}
