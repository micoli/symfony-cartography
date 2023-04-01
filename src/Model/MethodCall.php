<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Model;

use JsonSerializable;
use Micoli\SymfonyCartography\DataStructures\MethodCallArguments;

final class MethodCall implements JsonSerializable
{
    private string $method2methodHash;
    private string $class2classHash;

    public function __construct(
        public readonly MethodName $from,
        public readonly MethodName $to,
        public readonly MethodCallArguments $arguments,
        public readonly ?string $label,
    ) {
        $this->method2methodHash = sprintf('%s-%s', $from->__toString(), $to->__toString());
        $this->class2classHash = sprintf('%s-%s', $from->namespacedName, $to->namespacedName);
    }

    public function __toString(): string
    {
        return sprintf(
            '%s => %s (%d)',
            (string) $this->from,
            (string) $this->to,
            $this->label ?? '',
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'arguments' => $this->arguments,
            'label' => $this->label,
        ];
    }

    public function getMethod2methodHash(): string
    {
        return $this->method2methodHash;
    }

    public function getClass2classHash(): string
    {
        return $this->class2classHash;
    }
}
