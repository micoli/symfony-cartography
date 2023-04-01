<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Micoli\SymfonyCartography\Model\MethodCall;
use Ramsey\Collection\AbstractSet;

/**
 * @extends AbstractSet<MethodCall>
 *
 * @implements IteratorAggregate<MethodCall>
 */
final class MethodCalls extends AbstractSet implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getType(): string
    {
        return MethodCall::class;
    }
}
