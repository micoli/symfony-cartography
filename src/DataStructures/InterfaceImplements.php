<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Ramsey\Collection\Map\AbstractTypedMap;

/**
 * @extends AbstractTypedMap<string, Classes>
 *
 * @implements IteratorAggregate<string, Classes>
 */
final class InterfaceImplements extends AbstractTypedMap implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return Classes::class;
    }

    /**
     * @param class-string $interface
     * @param class-string $implement
     */
    public function addImplements(string $interface, string $implement): void
    {
        $this->putIfAbsent($interface, new Classes());
        /** @psalm-suppress PossiblyNullReference */
        $this->get($interface)->add($implement);
    }
}
