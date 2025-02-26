<?php

namespace App\Controller;

use App\Model\RegistrationRequestDto;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function registerAction(#[MapRequestPayload] RegistrationRequestDto $request): JsonResponse
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            $user = $this->userService->createUser($request->email, $request->password);
            $user->setRoles(['ROLE_USER']);

            $this->userService->saveUser($user);

            $serializedUser = $this->serializer->serialize($user, 'json', [
                'groups' => ['registration']
            ]);

            return new JsonResponse(['user' => json_decode($serializedUser)], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }
}