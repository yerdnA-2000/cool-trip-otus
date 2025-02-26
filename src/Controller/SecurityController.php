<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\LoginRequestDto;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly ManagerRegistry $doctrine,
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function login(#[MapRequestPayload] LoginRequestDto $request): JsonResponse
    {
        $user = $this->getUserByEmail($request->email);

        if (!$user || !$this->checkPassword($user, $request->password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Генерация JWT-токена
        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    /**
     * Using cache
     */
    private function getUserByEmail(string $email): ?User
    {
        return $this->cache->get("user_by_email_$email", function (ItemInterface $item) use ($email) {
            $item->tag('users');

            return $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        });
    }

    private function checkPassword(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $password): bool
    {
        return $this->passwordHasher->verify($user->getPassword(), $password);
    }
}