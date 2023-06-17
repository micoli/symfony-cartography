<?php

declare(strict_types=1);

namespace App\Domain\Specification\User;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(UserSpecificationInterface::class)]
interface UserSpecificationInterface
{
    public function isSatisfiedBy(UserSpecificationDTO $userValidationDTO): bool;
}
