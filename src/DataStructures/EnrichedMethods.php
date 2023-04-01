<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Ramsey\Collection\Map\AbstractTypedMap;

/**
 * @extends AbstractTypedMap<string, EnrichedMethod>
 *
 * @implements IteratorAggregate<string, EnrichedMethod>
 */
final class EnrichedMethods extends AbstractTypedMap implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return EnrichedMethod::class;
    }
}
