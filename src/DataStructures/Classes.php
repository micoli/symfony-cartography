<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Ramsey\Collection\AbstractSet;

/**
 * @extends AbstractSet<class-string>
 *
 * @implements IteratorAggregate<class-string>
 */
final class Classes extends AbstractSet implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getType(): string
    {
        return 'string';
    }
}
