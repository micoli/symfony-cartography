<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<MethodCallArgumentUnion>
 *
 * @implements IteratorAggregate<MethodCallArgumentUnion>
 */
final class MethodCallArguments extends AbstractCollection implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getType(): string
    {
        return MethodCallArgumentUnion::class;
    }
}
