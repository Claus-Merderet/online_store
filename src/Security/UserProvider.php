<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
readonly class UserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * @throws \Exception
     * todo: посмотреть как улучшить
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Проверяем, является ли идентификатор email иначе ищем по телефону
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findByEmail($identifier);
        } else {
            $user = $this->userRepository->findByPhone($identifier);
        }

        if (!$user) {
            throw new \Exception(sprintf('User with this credential "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return UserInterface::class === $class;
    }
}
