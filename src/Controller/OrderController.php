<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ChangeOrderStatusDTO;
use App\DTO\OrderDTO;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Entity\User;
use App\Factory\OrderFactory;
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
        private readonly OrderFactory $orderFactory,
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly UserFetcher $userFetcher,
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
        $error = $this->orderService->validateDTO($orderDTO);
        if ($error !== null) {
            return $error;
        }
        /** @var User $user */
        $user = $this->getUser();
        Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));

        $this->entityManager->beginTransaction();

        try {
            $order = $this->orderFactory->create($orderDTO, $user);
            $this->entityManager->persist($order);
            $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
            if ($cart instanceof Cart) {
                $this->entityManager->remove($cart);
            }
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(
                ['message' => 'Order created successfully. ID: ' . $order->getId()],
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();

            throw new \RuntimeException('Transaction failed: ' . $e->getMessage());
        }
    }

    #[Route('/api/orders', name: 'order_index', methods: ['GET'])]
    #[OA\Get(summary: 'Index order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can view orders.')]
    #[Security(name: 'Bearer')]
    public function index(#[MapQueryParameter] int $userId = 0): JsonResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $userId = $userId === 0 ? $user->getId() : $userId;
        if ($userId !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse('Only administrators can view orders of other users', Response::HTTP_FORBIDDEN);
        }

        $orders = $this->orderRepository->createQueryBuilder('o')
            ->where('o.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getResult();

        $ordersData = $this->serializer->serialize($orders, 'json', ['groups' => 'order:index']);

        return new JsonResponse($ordersData, Response::HTTP_OK);
    }

    #[Route('/api/orders/{id}', name: 'order_show', methods: ['GET'])]
    #[OA\Get(summary: 'Show order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can view orders.')]
    #[Security(name: 'Bearer')]
    public function show(#[MapEntity(id: 'id')] Order $order): JsonResponse
    {
        $user = $this->userFetcher->getAuthUser();
        if ($order->getUser()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse('Only administrators can view orders of other users', Response::HTTP_FORBIDDEN);
        }
        $ordersData = $this->serializer->serialize($order, 'json', ['groups' => 'order:index']);

        return new JsonResponse($ordersData, Response::HTTP_OK);
    }

    #[Route('/api/orders', name: 'order_change_status', methods: ['PATCH'])]
    #[OA\Patch(summary: 'Change order status')]
    #[IsGranted('ROLE_ADMIN', message: 'Only auth user can view orders.')]
    #[Security(name: 'Bearer')]
    public function changeOrderStatus(#[MapRequestPayload] ChangeOrderStatusDTO $changeOrderStatusDTO): JsonResponse
    {
        $order = $this->orderRepository->find($changeOrderStatusDTO->orderId);
        if ($order === null) {
            return new JsonResponse('Order not found. ID: ' . $changeOrderStatusDTO->orderId, Response::HTTP_OK);
        }
        /** @var User $user */
        $user = $this->getUser();
        Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));

        $this->entityManager->beginTransaction();

        try {
            $statusHistory = new OrderStatusHistory($order, $changeOrderStatusDTO->statusName, '', $user);
            $order->addStatusHistory($statusHistory);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(status: Response::HTTP_OK);
        } catch (Exception $e) {
            $this->entityManager->rollback();

            return new JsonResponse(
                ['error' => 'Failed to change order status: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
