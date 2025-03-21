<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CartDTO;
use App\DTO\CartUpdateDTO;
use App\Entity\Cart;
use App\Entity\User;
use App\Enum\RoleName;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

#[OA\Tag(name: 'Cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly CartService $cartService,
    ) {
    }

    #[Route('/api/carts/{id}', name: 'cart_index', methods: ['GET'])]
    #[OA\Get(summary: 'Show cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can view cart.')]
    #[Security(name: 'Bearer')]
    public function index(#[MapEntity(id: 'id')] Cart $cart): JsonResponse
    {
        try {
            $this->cartService->checkPermissions($cart->getUser() === $this->getUser(), $this->isGranted(RoleName::ADMIN->value));
            $cartData = $this->serializer->serialize($cart, 'json', ['groups' => 'cart']);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to show cart: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

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
        try {
            /* @var User $user */
            $user = $this->getUser();
            Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));
            $this->cartService->checkExistCart($user);
            $this->cartService->fillProductsInDTO($cartDTO->cartItem);
            $cart = Cart::createFromDTO($cartDTO, $user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to create cart: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return new JsonResponse(['cartId' => $cart->getId()], Response::HTTP_OK);
    }

    #[Route('/api/carts/{id}', name: 'cart_delete', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete cart')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can delete cart.')]
    #[Security(name: 'Bearer')]
    public function delete(#[MapEntity(id: 'id')] Cart $cart): JsonResponse
    {
        try {
            $this->cartService->checkPermissions($cart->getUser() === $this->getUser(), $this->isGranted(RoleName::ADMIN->value));
            $this->entityManager->remove($cart);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to delete cart: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

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
        try {
            $this->cartService->checkPermissions($cart->getUser() === $this->getUser(), $this->isGranted(RoleName::ADMIN->value));
            $this->cartService->fillProductsInDTO($cartUpdateDTO->cartItemUpdateDTO);
            $cart->updateCartItemFromDTO($cartUpdateDTO);
            $this->entityManager->flush();
            $cartData = $this->serializer->serialize($cart, 'json', ['groups' => 'cart']);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to update cart: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return new JsonResponse($cartData, Response::HTTP_OK);
    }
}
