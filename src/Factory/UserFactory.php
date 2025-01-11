<?php declare(strict_types=1);

namespace App\Factory;

use App\DTO\RegisterUserDTO;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function create(RegisterUserDTO $registerUserDTO, Role $role): User
    {
        $user = new User($registerUserDTO, $role);
        $user->setPassword($registerUserDTO->password, $this->passwordHasher);

        return $user;
    }
}