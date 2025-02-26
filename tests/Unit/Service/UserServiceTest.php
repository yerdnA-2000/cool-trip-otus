<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    public function testCreateUser(): void
    {
        $email = 'newuser@example.com';
        $password = 'strongpassword';

        $passwordHasherMock = self::createMock(UserPasswordHasherInterface::class);
        $passwordHasherMock->expects(self::once())
            ->method('hashPassword')
            ->willReturn('hashedpassword');

        $doctrineMock = self::createMock(ManagerRegistry::class);
        $repositoryMock = self::createMock(ObjectRepository::class);
        $repositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        $doctrineMock->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repositoryMock);

        $userService = new UserService($doctrineMock, $passwordHasherMock);

        $user = $userService->createUser($email, $password);

        self::assertInstanceOf(User::class, $user);
        self::assertEquals($email, $user->getEmail());
    }

    public function testCreateUserWithExistingEmail(): void
    {
        $email = 'existing@example.com';
        $password = 'strongpassword';

        $doctrineMock = self::createMock(ManagerRegistry::class);
        $repositoryMock = self::createMock(ObjectRepository::class);
        $repositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(new User());

        $doctrineMock->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repositoryMock);

        $userService = new UserService($doctrineMock, self::createMock(UserPasswordHasherInterface::class));

        self::expectException(\DomainException::class);
        self::expectExceptionMessage('User with this email already exists');

        $userService->createUser($email, $password);
    }
}