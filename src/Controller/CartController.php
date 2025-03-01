<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CartDto;
use App\DTO\CartUpdateDTO;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Enum\ItemActionType;
use App\Enum\RoleName;
use App\Factory\CartFactory;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use RuntimeException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly CartFactory $cartFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly CartService $cartService,
        private readonly ProductRepository $productRepository,
        private readonly CartItemRepository $cartItemRepository,
    ) {
    }

    #[Route('/api/carts/{id}', name: 'cart_index', methods: ['GET'])]
    #[OA\Get(summary: 'Show cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can view cart.')]
    #[Security(name: 'Bearer')]
    public function index(#[MapEntity(id: 'id')] Cart $cart): JsonResponse
    {
        if ($cart->getUser() !== $this->getUser() && $this->isGranted(RoleName::ADMIN->value) === false) {
            return new JsonResponse(
                ['error' => 'You do not have sufficient permissions to perform this action.'],
                Response::HTTP_FORBIDDEN,
            );
        }
        $cartData = $this->serializer->serialize($cart, 'json', ['groups' => 'cart']);

        return new JsonResponse($cartData, Response::HTTP_OK);
    }

    #[Route('/api/carts', name: 'cart_create', methods: ['POST'])]
    #[OA\Post(summary: 'Create cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can create cart.')]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
        )] CartDTO $cartDTO,
    ): JsonResponse {
        $user = $this->getUser();
        if ($this->cartRepository->findByUser($user) !== null) {
            return new JsonResponse(['error' => 'Cart already exists'], Response::HTTP_BAD_REQUEST);
        }
        if (($errors = $this->cartService->fillProductsDTO($cartDTO)) !== null) {
            return $errors;
        }

        $cart = $this->cartFactory->create($cartDTO, $user);
        $this->entityManager->flush();

        return new JsonResponse(['cartId' => $cart->getId()], Response::HTTP_OK);
    }

    #[Route('/api/carts/{id}', name: 'cart_delete', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can delete cart.')]
    #[Security(name: 'Bearer')]
    public function delete(#[MapEntity(id: 'id')] Cart $cart): JsonResponse
    {
        if ($cart->getUser() !== $this->getUser() && $this->isGranted(RoleName::ADMIN->value) === false) {
            return new JsonResponse(
                ['error' => 'You do not have sufficient permissions to perform this action.'],
                Response::HTTP_FORBIDDEN,
            );
        }
        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Successfully deleted'], Response::HTTP_OK);
    }

    #[Route('/api/carts/{id}', name: 'cart_update', methods: ['PUT'])]
    #[OA\Put(summary: 'Update cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can delete cart.')]
    #[Security(name: 'Bearer')]
    public function update(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
        )] CartUpdateDTO $cartUpdateDTO,
        #[MapEntity(id: 'id')] Cart $cart,
    ): JsonResponse {
        if ($cart->getUser() !== $this->getUser() && $this->isGranted(RoleName::ADMIN->value) === false) {
            return new JsonResponse(
                ['error' => 'You do not have sufficient permissions to perform this action.'],
                Response::HTTP_FORBIDDEN,
            );
        }
        $this->entityManager->beginTransaction();

        try {
            foreach ($cartUpdateDTO->cartItemUpdateDTO as $cartItemUpdateDTO) {
                $product = $this->productRepository->find($cartItemUpdateDTO->productId);
                if ($product === null) {
                    throw new RuntimeException('Product not found. ID: ' . $cartItemUpdateDTO->productId);
                }
                $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'product' => $product,]);
                if ($cartItemUpdateDTO->action === ItemActionType::ADD) {
                    if ($cartItem instanceof CartItem) {
                        $cartItem->setQuantity($cartItem->getQuantity() + $cartItemUpdateDTO->quantity);
                    } else {
                        $cartItem = new CartItem($cart, $product, $cartItemUpdateDTO->quantity);
                    }
                    $this->entityManager->persist($cartItem);
                } elseif ($cartItemUpdateDTO->action === ItemActionType::REMOVE) {
                    if ($cartItem instanceof CartItem) {
                        $newAmount = $cartItem->getQuantity() - $cartItemUpdateDTO->quantity;
                        if ($newAmount < 1) {
                            $this->entityManager->remove($cartItem);
                            $cart->removeCartItem($cartItem);
                        } else {
                            $cartItem->setQuantity($newAmount);
                            $this->entityManager->persist($cartItem);
                        }
                    } else {
                        throw new RuntimeException('The product to be deleted was not found in the cart. ID: ' . $cartItemUpdateDTO->productId);
                    }
                }
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();

            return new JsonResponse(
                ['error' => 'Failed to update cart: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $cartData = $this->serializer->serialize($cart, 'json', ['groups' => 'cart']);

        return new JsonResponse($cartData, Response::HTTP_OK);
    }
}
