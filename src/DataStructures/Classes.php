<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use JsonSerializable;
use Micoli\Multitude\Set\MutableSet;

/**
 * @template-extends MutableSet<class-string>
 */
final class Classes extends MutableSet implements JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return '';
    }
}
