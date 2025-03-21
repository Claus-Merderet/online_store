<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserService
{
    private const REFRESH_TOKEN_LIVE = 3600;

    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private JWTTokenManagerInterface $JWTTokenManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function registerUser(RegisterUserDTO $registerUserDTO): User
    {
        $role = $this->roleRepository->getRoleUser();
        $user = User::createFromDTO($registerUserDTO, $role, $this->passwordHasher);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

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

    public function validateDTO(RegisterUserDTO $registerUserDTO): void
    {
        if (!empty($registerUserDTO->email) && $this->userRepository->findOneByEmail($registerUserDTO->email)) {
            throw new RuntimeException('User with this email already exists.');
        }

        if (!empty($registerUserDTO->phone) && $this->userRepository->findOneByPhone($registerUserDTO->phone)) {
            throw new RuntimeException('User with this phone number already exists.');
        }
    }
}
