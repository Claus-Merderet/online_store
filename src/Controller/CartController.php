<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CartItemDTO;
use App\Factory\CartFactory;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly CartFactory $cartFactory,
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[Route('/api/cart', name: 'cart_create', methods: ['POST'])]
    #[OA\Put(summary: 'Create cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can create cart.')]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
        )] CartItemDTO $cartItemDTO,
    ): JsonResponse {
        $product = $this->productRepository->find($cartItemDTO->productId);
        $user = $this->getUser();
        if ($product === null) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_BAD_REQUEST);
        }
        if ($this->cartRepository->findByUser($user) !== null) {
            return new JsonResponse(['error' => 'Cart already exists'], Response::HTTP_BAD_REQUEST);
        }
        $cart = $this->cartFactory->create($cartItemDTO, $product, $user);

        $this->cartRepository->save($cart);

        return new JsonResponse(['cartId' => $cart->getId()], status: Response::HTTP_OK);
    }
}
