<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * Создание нового пользователя.
     */
    public function createUser(string $email, string $password): User
    {
        $existingUser = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            throw new \DomainException('User with this email already exists');
        }

        $user = new User();
        $user->setEmail($email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        return $user;
    }

    /**
     * Save user in DB
     */
    public function saveUser(User $user): void
    {
        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();
    }
}