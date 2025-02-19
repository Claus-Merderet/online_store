<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CartDto;
use App\Entity\Cart;
use App\Factory\CartFactory;
use App\Repository\CartRepository;
use App\Service\CartService;
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
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly CartFactory $cartFactory,
        private readonly  EntityManagerInterface $entityManager,
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
        $errors = $this->cartService->checkPermission($cart->getUser(), $this->getUser(), $this->isGranted('ROLE_ADMIN'));
        if ($errors !== null) {
            return $errors;
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
        if (($errors = $this->cartService->validateDTO($cartDTO)) !== null) {
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
        $errors = $this->cartService->checkPermission($cart->getUser(), $this->getUser(), $this->isGranted('ROLE_ADMIN'));
        if ($errors !== null) {
            return $errors;
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
        )] CartDTO $cartDTO,
        #[MapEntity(id: 'id')] Cart $cart,
    ): JsonResponse {
        if (($errors = $this->cartService->validateDTO($cartDTO)) !== null) {
            return $errors;
        }
        $errors = $this->cartService->checkPermission($cart->getUser(), $this->getUser(), $this->isGranted('ROLE_ADMIN'));
        if ($errors !== null) {
            return $errors;
        }

        $cart->syncWithDTO($cartDTO);// TODO: пересмотреть логику
        $this->entityManager->flush();
        $cartData = $this->serializer->serialize($cart, 'json', ['groups' => 'cart']);

        return new JsonResponse($cartData, Response::HTTP_OK);
    }
}
