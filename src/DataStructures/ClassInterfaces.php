<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use Ramsey\Collection\Map\AbstractTypedMap;

/**
 * @extends AbstractTypedMap<string, Classes>
 *
 * @implements IteratorAggregate<string, Classes>
 */
final class ClassInterfaces extends AbstractTypedMap implements IteratorAggregate
{
    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return Classes::class;
    }

    /**
     * @param class-string $class
     * @param class-string $interface
     */
    public function addInterface(string $class, string $interface): void
    {
        $this->putIfAbsent($class, new Classes());
        /** @psalm-suppress PossiblyNullReference */
        $this->get($class)->add($interface);
    }
}
