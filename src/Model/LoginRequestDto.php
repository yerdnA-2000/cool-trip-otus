<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,

        #[Assert\NotBlank]
        #[\SensitiveParameter]
        public readonly string $password,
    ) {
    }
}