<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email): User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function findOneByPhone(string $phone): User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);
    }

    public function findById(string $id): User|null
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    public function deleteById(string $id): User|null
    {
        $user = $this->findById($id);

        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
