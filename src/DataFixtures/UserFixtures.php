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
    private const USERS_DATA = [
        [
            'phone' => '+7999999999',
            'email' => 'justuser@example.com',
            'password' => 'test123!',
            'promoId' => '',
            'role' => RoleFixtures::ROLE_USER,
        ],
        [
            'phone' => '+79493223333',
            'email' => 'admin@example.com',
            'password' => 'admin123!',
            'promoId' => '',
            'role' => RoleFixtures::ROLE_ADMIN,
        ],
        [
            'phone' => '+7848483384',
            'email' => 'super_admin@example.com',
            'password' => 'test123!',
            'promoId' => '',
            'role' => RoleFixtures::ROLE_SUPER_ADMIN,
        ],
    ];

    public function __construct(
        private readonly UserFactory $userFactory,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS_DATA as $userData) {
            $dto = new RegisterUserDTO(
                $userData['phone'],
                $userData['email'],
                $userData['password'],
                $userData['promoId'],
            );
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
}
