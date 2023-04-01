<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Micoli\SymfonyCartography\Model\MethodCallArgument;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<MethodCallArgument>
 *
 * @implements IteratorAggregate<MethodCallArgument>
 */
final class MethodCallArgumentUnion extends AbstractCollection implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getType(): string
    {
        return MethodCallArgument::class;
    }
}
