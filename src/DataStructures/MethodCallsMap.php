<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Ramsey\Collection\Map\AbstractTypedMap;

/**
 * @extends AbstractTypedMap<string, MethodCalls>
 *
 * @implements IteratorAggregate<string, MethodCalls>
 **/
final class MethodCallsMap extends AbstractTypedMap implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return MethodCalls::class;
    }
}
