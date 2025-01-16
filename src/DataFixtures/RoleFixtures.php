<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const USER_ROLE = 'USER_ROLE';

    public const ADMIN_ROLE = 'ADMIN_ROLE';

    public const SUPER_ADMIN_ROLE = 'SUPER_ADMIN_ROLE';

    private const ROLES = [
        self::USER_ROLE,
        self::ADMIN_ROLE,
        self::SUPER_ADMIN_ROLE,
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
