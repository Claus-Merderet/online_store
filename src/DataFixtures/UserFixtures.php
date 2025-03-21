<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\RegisterUserDTO;
use App\Entity\Role;
use App\Entity\User;
use App\Enum\RoleName;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_PHONE = '+7999999999';

    public const USER_EMAIL = 'justuser@example.com';

    public const USER_PASSWORD = 'test123!';

    public const ADMIN_PHONE = '+79493223333';

    public const ADMIN_EMAIL = 'admin@example.com';

    public const ADMIN_PASSWORD = 'admin123!';

    public const SUPER_ADMIN_PHONE = '+7848483384';

    public const SUPER_ADMIN_EMAIL = 'super_admin@example.com';

    public const SUPER_ADMIN_PASSWORD = 'test123!';

    public const USER_REFERENCE = 'user-';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::getUsersData() as $index => $userData) {
            $registerUserDTO = $this->createRegisterUserDTO($userData);
            $role = $this->getReference($userData['role'], Role::class);
            $user = User::createFromDTO($registerUserDTO, $role, $this->passwordHasher);
            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE . $index, $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }

    /**
     * @return array<array{
     *     phone: string,
     *     email: string,
     *     password: string,
     *     promoId: string,
     *     role: string
     * }>
     */
    private static function getUsersData(): array
    {
        return [
            [
                'phone' => self::USER_PHONE,
                'email' => self::USER_EMAIL,
                'password' => self::USER_PASSWORD,
                'promoId' => '',
                'role' => RoleName::USER->value,
                'name' => 'Max',
            ],
            [
                'phone' => self::ADMIN_PHONE,
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'promoId' => '',
                'role' => RoleName::ADMIN->value,
                'name' => 'Sam',
            ],
            [
                'phone' => self::SUPER_ADMIN_PHONE,
                'email' => self::SUPER_ADMIN_EMAIL,
                'password' => self::SUPER_ADMIN_PASSWORD,
                'promoId' => '',
                'role' => RoleName::SUPER_ADMIN->value,
                'name' => 'Bob',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function createRegisterUserDTO(array $userData): RegisterUserDTO
    {
        return new RegisterUserDTO(
            $userData['phone'],
            $userData['email'],
            $userData['password'],
            $userData['promoId'],
            $userData['name'],
        );
    }
}
