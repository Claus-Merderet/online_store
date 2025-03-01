<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use App\Enum\RoleName;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    private const ROLES = [
        RoleName::USER->value,
        RoleName::ADMIN->value,
        RoleName::SUPER_ADMIN->value,
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
