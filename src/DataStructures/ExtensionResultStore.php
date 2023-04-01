<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\Map\AbstractTypedMap;

/**
 * @extends AbstractTypedMap<class-string, AbstractCollection>
 *
 * @implements IteratorAggregate<class-string, AbstractCollection>
 */
final class ExtensionResultStore extends AbstractTypedMap implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return AbstractCollection::class;
    }
}