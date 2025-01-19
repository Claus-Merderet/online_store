<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const ROLE_USER = 'ROLE_USER';

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    private const ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROLES as $roleName) {
            $role = new Role($roleName);
            $manager->persist($role);
            $this->addReference($roleName, $role);
        }

        $manager->flush();
    }
}
