<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\RegisterUserDTO;
use App\Entity\Role;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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

    public function __construct(
        private readonly UserFactory $userFactory,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::getUsersData() as $userData) {
            $dto = $this->createRegisterUserDTO($userData);
            $role = $this->getReference($userData['role'], Role::class);
            $user = $this->userFactory->create($dto, $role);
            $manager->persist($user);
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
     * @return array<array{phone: string, email: string, password: string, promoId: string, role: string}>
     */
    private static function getUsersData(): array
    {
        return [
            [
                'phone' => self::USER_PHONE,
                'email' => self::USER_EMAIL,
                'password' => self::USER_PASSWORD,
                'promoId' => '',
                'role' => RoleFixtures::ROLE_USER,
                'name' => 'Max',
            ],
            [
                'phone' => self::ADMIN_PHONE,
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'promoId' => '',
                'role' => RoleFixtures::ROLE_ADMIN,
                'name' => 'Sam',
            ],
            [
                'phone' => self::SUPER_ADMIN_PHONE,
                'email' => self::SUPER_ADMIN_EMAIL,
                'password' => self::SUPER_ADMIN_PASSWORD,
                'promoId' => '',
                'role' => RoleFixtures::ROLE_SUPER_ADMIN,
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
