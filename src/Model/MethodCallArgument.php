<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Model;

use JsonSerializable;

final class MethodCallArgument implements JsonSerializable
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $value,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '%s => %s',
            $this->type,
            ($this->value === null) ? 'NULL' : (string) $this->value,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
        ];
    }
}
