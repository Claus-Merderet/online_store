<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{
    private const REFRESH_TOKEN_LIVE = 3600;

    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private ValidatorInterface $validator,
        private UserFactory $userFactory,
        private JWTTokenManagerInterface $JWTTokenManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
    ) {
    }

    public function registerUser(RegisterUserDTO $registerUserDTO): User
    {
        $role = $this->roleRepository->getRoleUser();
        $user = $this->userFactory->create($registerUserDTO, $role);
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @return array{
     *     access_token: string,
     *     refresh_token: string
     *     }
     */
    public function generateTokens(User $user): array
    {
        $accessToken = $this->JWTTokenManager->create($user);
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            self::REFRESH_TOKEN_LIVE,
        );
        $this->refreshTokenManager->save($refreshToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->getRefreshToken(),
        ];
    }

    public function validateDTO(RegisterUserDTO $dto): JsonResponse|null
    {
        $errors = $this->validator->validate($dto);// TODO: похоже не надо

        if (count($errors) > 0) {
            return $this->createValidationErrorResponse($errors);
        }
        if (!empty($dto->email) && $this->userRepository->findOneByEmail($dto->email)) {
            return new JsonResponse(['error' => 'User with this email already exists.'], Response::HTTP_CONFLICT);
        }

        if (!empty($dto->phone) && $this->userRepository->findOneByPhone($dto->phone)) {
            return new JsonResponse(
                ['error' => 'User with this phone number already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        return null;
    }

    private function createValidationErrorResponse(ConstraintViolationListInterface $violations): JsonResponse
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new JsonResponse(
            ['error' => 'Validation failed', 'errors' => $errors],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
