<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Model;

final class MethodName
{
    /** @param class-string $namespacedName */
    public function __construct(
        public readonly string $namespacedName,
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '%s::%s',
            $this->namespacedName,
            $this->name,
        );
    }

    public function equals(self $compared): bool
    {
        return $this->namespacedName === $compared->namespacedName && $this->name === $compared->name;
    }

    public static function fromNamespacedMethod(string $namespacedMethod): self
    {
        if (str_contains($namespacedMethod, '::')) {
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$namespacedName, $method] = explode('::', $namespacedMethod, 2);
        } else {
            [$namespacedName, $method] = [$namespacedMethod, '__invoke'];
        }

        /** @var class-string $namespacedName */
        return new self($namespacedName, $method);
    }

    public function jsonSerialize(): array
    {
        return [
            'namespacedName' => $this->namespacedName,
            'name' => $this->name,
        ];
    }
}
