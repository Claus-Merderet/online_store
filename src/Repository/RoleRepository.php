<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use App\Enum\RoleName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Role::class);
    }

    public function getRoleUser(): ?Role
    {
        return $this->entityManager->getRepository(Role::class)->findOneBy(['roleName' => RoleName::USER->value]);
    }

    public function getRoleAdmin(): ?Role
    {
        return $this->entityManager->getRepository(Role::class)->findOneBy(['roleName' => RoleName::ADMIN->value]);
    }

    public function getRoleSuperAdmin(): ?Role
    {
        return $this->entityManager->getRepository(Role::class)->findOneBy(['roleName' => RoleName::SUPER_ADMIN->value]);
    }
}
