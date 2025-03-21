<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\OrderChangeStatusDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderUpdateDTO;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\RoleName;
use App\Repository\OrderRepository;
use App\Security\UserFetcher;
use App\Service\OrderService;
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
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

#[OA\Tag(name: 'Order')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderRepository $orderRepository,
        private readonly SerializerInterface $serializer,
        private readonly UserFetcher $userFetcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/orders', name: 'order_create', methods: ['POST'])]
    #[OA\Post(summary: 'Create order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can create products.')]
    #[Security(name: 'Bearer')]
    public function create(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
    )] OrderDTO $orderDTO): JsonResponse
    {
        try {
            /* @var User $user */
            $user = $this->getUser();
            Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));
            $this->orderService->fillOrderProductsInDTO($orderDTO->orderProductsDTO);
            $this->orderService->validateDTO($orderDTO);
            $order = Order::createFromDTO($orderDTO, $user);
            $this->entityManager->persist($order);
            $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
            if ($cart instanceof Cart) {
                $this->entityManager->remove($cart);
            }
            $this->entityManager->flush();

            return new JsonResponse(
                ['message' => 'Order created successfully. ID: ' . $order->getId()],
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to create order: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/api/orders', name: 'order_index', methods: ['GET'])]
    #[OA\Get(summary: 'Index order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can view orders.')]
    #[Security(name: 'Bearer')]
    public function index(#[MapQueryParameter] int $userId = 0): JsonResponse
    {
        try {
            $user = $this->userFetcher->getAuthUser();
            $userId = $userId === 0 ? $user->getId() : $userId;
            $this->orderService->checkPermissions($userId === $user->getId(), $this->isGranted(RoleName::ADMIN->value));
            $orders = $this->orderRepository->findBy(['user' => $user]);

            return new JsonResponse(
                $this->serializer->serialize($orders, 'json', ['groups' => 'order:index']),
                Response::HTTP_OK,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to index order: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/api/orders/{id}', name: 'order_show', methods: ['GET'])]
    #[OA\Get(summary: 'Show order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can view orders.')]
    #[Security(name: 'Bearer')]
    public function show(#[MapEntity(id: 'id')] Order $order): JsonResponse
    {
        try {
            $user = $this->userFetcher->getAuthUser();
            $this->orderService->checkPermissions($order->getUser()->getId() === $user->getId(), $this->isGranted(RoleName::ADMIN->value));

            return new JsonResponse(
                $this->serializer->serialize($order, 'json', ['groups' => 'order:index']),
                Response::HTTP_OK,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to show order: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/api/orders', name: 'order_change_status', methods: ['PATCH'])]
    #[OA\Patch(summary: 'Change order status')]
    #[IsGranted(RoleName::ADMIN->value, message: 'Only admin can change order status.')]
    #[Security(name: 'Bearer')]
    public function changeOrderStatus(#[MapRequestPayload] OrderChangeStatusDTO $changeOrderStatusDTO): JsonResponse
    {
        try {
            $order = $this->orderService->findOrder($changeOrderStatusDTO->orderId);
            $user = $this->userFetcher->getAuthUser();
            /* @var User $user */
            Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));
            $order->addStatusHistory($changeOrderStatusDTO->statusName, $changeOrderStatusDTO->comment, $user);

            return new JsonResponse(status: Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to change order status: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    #[Route('/api/orders/{id}', name: 'order_update', methods: ['PUT'])]
    #[OA\Put(summary: 'Update order')]
    #[IsGranted(RoleName::ADMIN->value, message: 'Only admin can change order.')]
    #[Security(name: 'Bearer')]
    public function update(
        #[MapRequestPayload] OrderUpdateDTO $updateOrderDTO,
        #[MapEntity(id: 'id', message: 'The order does not exist')] Order $order,
    ): JsonResponse {
        try {
            $this->orderService->fillOrderProductsInDTO($updateOrderDTO->updateOrderItems);
            $order->updateFromDTO($updateOrderDTO);
            $this->entityManager->flush();

            return new JsonResponse(
                $this->serializer->serialize($order, 'json', ['groups' => 'order:index']),
                Response::HTTP_OK,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to update order: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
