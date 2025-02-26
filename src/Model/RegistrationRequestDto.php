<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 4096)]
        #[\SensitiveParameter]
        public readonly string $password,
    ) {
    }
}