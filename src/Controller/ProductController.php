<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ProductDTO;
use App\Service\ProductService;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: "Product")]
class ProductController extends AbstractController
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    #[OA\Get(summary: "Return a product list")]
    public function index(#[MapQueryParameter] int $page): JsonResponse
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
            ]
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
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST
    )] ProductDTO $productDTO): JsonResponse
    {
        $validationErrors = $this->productService->validateDTO($productDTO);
        if ($validationErrors !== null) {
            return $validationErrors;
        }
        if ($this->productService->productExists($productDTO->id)) {
            return new JsonResponse(
                ['error' => 'Product with this ID already exists: ' . $productDTO->id],
                Response::HTTP_BAD_REQUEST
            );
        }
        $product = $this->productService->createProduct($productDTO);

        return new JsonResponse(
            ['message' => 'Product created successfully', 'id' => $product->getId()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/products', name: 'product_update', methods: ['PUT'])]
    #[OA\Put(summary: "Update a product")]
    #[IsGranted('ROLE_ADMIN', message: 'Only admins can create products.')]
    #[Security(name: 'Bearer')]
    public function update(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST
    )] ProductDTO $productDTO): JsonResponse
    {
        $validationErrors = $this->productService->validateDTO($productDTO);
        if ($validationErrors !== null) {
            return $validationErrors;
        }
        $product = $this->productService->findProduct($productDTO->id);

        if (!$product) {
            return new JsonResponse(
                ['error' => 'Product not found', 'id' => $productDTO->id],
                Response::HTTP_BAD_REQUEST
            );
        }
        $this->productService->updateProduct($product, $productDTO);

        return new JsonResponse(
            ['message' => 'Product updated successfully'],
            Response::HTTP_ACCEPTED
        );
    }
}
