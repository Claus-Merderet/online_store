<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CartDto;
use App\Entity\Cart;
use App\Factory\CartFactory;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
        private readonly  EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/carts', name: 'cart_create', methods: ['POST'])]
    #[OA\Put(summary: 'Create cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can create cart.')]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
        )] CartDTO $cartDTO,
    ): JsonResponse {
        foreach ($cartDTO->cartItems as $item) {
            $product = $this->productRepository->find($item->productId);
            if ($product === null) {
                return new JsonResponse(['error' => 'Product not found. ID:' . $item->productId], Response::HTTP_BAD_REQUEST);
            }
            $item->product = $product;
        }
        $user = $this->getUser();
        if ($this->cartRepository->findByUser($user) !== null) {
            return new JsonResponse(['error' => 'Cart already exists'], Response::HTTP_BAD_REQUEST);
        }
        $cart = $this->cartFactory->create($cartDTO, $user);

        $this->cartRepository->save($cart);

        return new JsonResponse(['cartId' => $cart->getId()], Response::HTTP_OK);
    }

    #[Route('/api/carts/{id}', name: 'cart_delete', methods: ['DELETE'])]
    #[OA\Put(summary: 'Delete cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can delete cart.')]
    #[Security(name: 'Bearer')]
    public function delete(#[MapEntity(id: 'id')] Cart $cart): JsonResponse
    {
        if ($cart->getUser() !== $this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN') === false) {
                return new JsonResponse(
                    ['error' => 'You do not have sufficient permissions to perform this action.'],
                    Response::HTTP_FORBIDDEN,
                );
            }
        }
        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
    }
}
