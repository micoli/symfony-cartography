<?php

declare(strict_types=1);

namespace App\Domain\Specification\User;

use App\Domain\Repository\UserRepositoryInterface;

final readonly class UserSpecificationIsUnique implements UserSpecificationInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function isSatisfiedBy(UserSpecificationDTO $userValidationDTO): bool
    {
        if ($this->userRepository->findOneByUsername($userValidationDTO->username) !== null) {
            return false;
        }

        return true;
    }
}
