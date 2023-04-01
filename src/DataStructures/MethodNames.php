<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Micoli\SymfonyCartography\Model\MethodName;
use Ramsey\Collection\AbstractSet;

/**
 * @extends AbstractSet<MethodName>
 *
 * @implements IteratorAggregate<MethodName>
 */
final class MethodNames extends AbstractSet implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getType(): string
    {
        return MethodName::class;
    }
}
