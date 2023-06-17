<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Model;

use JsonSerializable;
use Micoli\SymfonyCartography\DataStructures\MethodCalls;

final class EnrichedMethod implements JsonSerializable
{
    private MethodCalls $methodCalls;

    /** @param array<int, list<class-string>> $arguments */
    public function __construct(
        public readonly MethodName $methodName,
        public readonly array $arguments,
        public readonly bool $definedInternally,
    ) {
        $this->methodCalls = new MethodCalls();
    }

    public function addMethodCalls(MethodCall ...$calls): void
    {
        array_map(fn (MethodCall $call) => $this->methodCalls->append($call), $calls);
    }

    public function getMethodCalls(): MethodCalls
    {
        return $this->methodCalls;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s::%s',
            $this->methodName->namespacedName,
            $this->methodName->name,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'methodName' => $this->methodName,
            'calls' => $this->methodCalls->toArray(),
        ];
    }
}
