<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\RegisterUserDTO;
use App\Enum\NotificationType;
use App\Security\UserFetcher;
use App\Service\NotificationService;
use App\Service\UserService;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly NotificationService $notificationService,
        private readonly UserFetcher $userFetcher,
    ) {
    }

    #[Route('/api/users/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        description: 'This endpoint registers a new user and returns tokens upon successful registration.',
        summary: 'Register a new user',
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Returns a message with tokens.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'User registered and authenticated successfully.',
                ),
                new OA\Property(
                    property: 'tokens',
                    properties: [
                        new OA\Property(
                            property: 'access_token',
                            type: 'string',
                            example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
                        ),
                        new OA\Property(
                            property: 'refresh_token',
                            type: 'string',
                            example: 'dGhpcyBpcyBhIHJlZnJlc2ggdG9rZW4uLi4=',
                        ),
                    ],
                    type: 'object',
                ),
            ],
        ),
    )]
    public function register(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
        )] RegisterUserDTO $registerUserDTO,
    ): JsonResponse {
        $validationErrors = $this->userService->validateDTO($registerUserDTO);
        if ($validationErrors !== null) {
            return $validationErrors;
        }
        $notificationType = $registerUserDTO->email ? NotificationType::EMAIL : NotificationType::SMS;
        $recipient = $registerUserDTO->email ?? $registerUserDTO->phone;

        $user = $this->userService->registerUser($registerUserDTO);
        $tokens = $this->userService->generateTokens($user);
        $this->notificationService->sendNotification($notificationType, $recipient, $registerUserDTO->promoId);

        return new JsonResponse([
            'message' => 'User registered and authenticated successfully.',
            'user_id' => $user->getId(),
            'tokens' => [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/users/me', name: 'api_user_me', methods: ['GET'])]
    #[OA\Get(
        operationId: 'api_user_me',
        description: 'Returns information about the authorized user.',
        summary: 'Get authenticated user information',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Returns user information.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 123),
                new OA\Property(property: 'phone', type: 'string', example: '+79696969696'),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
            ],
        ),
    )]
    #[Security(name: 'Bearer')]
    public function getAuthenticatedUser(): JsonResponse
    {
        $user = $this->userFetcher->getAuthUser();

        return new JsonResponse([
            'id' => $user->getId(),
            'phone' => $user->getPhone(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    #[Route('/api/users/logout', name: 'api_user_logout', methods: ['POST'])]
    public function logoutUser(): JsonResponse
    {
        //$resp = $this->security->logout();
        //TODO: не работает. похоже не нужно, фронт просто будет забывать accessToken и refreshToken или все же нет

        return new JsonResponse(Response::HTTP_OK);
    }
}
