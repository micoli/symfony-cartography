<?php

declare(strict_types=1);

namespace App\Domain\Specification\User;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class UserSpecification
{
    /** @param iterable<UserSpecificationInterface> $specificationsIterator */
    public function __construct(
        #[TaggedIterator(UserSpecificationInterface::class)]
        private iterable $specificationsIterator,
    ) {
    }

    public function isSatisfiedBy(UserSpecificationDTO $userValidationDTO): bool
    {
        foreach ($this->specificationsIterator as $specification) {
            if (!$specification->isSatisfiedBy($userValidationDTO)) {
                return false;
            }
        }

        return true;
    }
}
