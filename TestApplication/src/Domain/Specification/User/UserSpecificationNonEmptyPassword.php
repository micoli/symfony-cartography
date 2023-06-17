<?php

declare(strict_types=1);

namespace App\Domain\Specification\User;


final readonly class UserSpecificationNonEmptyPassword implements UserSpecificationInterface
{
    public function __construct(
    ) {
    }

    public function isSatisfiedBy(UserSpecificationDTO $userValidationDTO): bool
    {
        $password = $userValidationDTO->password;
        if ($password === null) {
            return true;
        }

        $password = trim($password);

        if (!(strlen($password) > 4)) {

            return false;
        }

        return true;
    }
}
