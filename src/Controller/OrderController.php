<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\OrderDTO;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Order')]
class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'order_create', methods: ['POST'])]
    #[OA\Put(summary: 'Create order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Only auth user can create products.')]
    #[Security(name: 'Bearer')]
    public function create(#[MapRequestPayload(
        acceptFormat: 'json',
        validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
    )] OrderDTO $orderDTO): JsonResponse
    {
        //$productDTO = new OrederDTO(json_decode($request->getContent(), true));
        return new JsonResponse(status: Response::HTTP_OK);
    }
}
