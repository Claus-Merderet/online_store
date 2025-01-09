<?php

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

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findByEmail(string $email): User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }
    public function findByPhone(string $phone): User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);
    }
    public function findById(string $id): User|null
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }
}
