<?php

declare(strict_types=1);

namespace App\Domain\Specification\User;

final readonly class UserSpecificationDTO
{
    public function __construct(
        public string $externalIdentifier,
        public string $username,
        public ?string $email,
        public ?string $password,
    ) {
    }
}
