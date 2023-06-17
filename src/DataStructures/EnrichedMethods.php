<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Map\MutableMap;
use Micoli\SymfonyCartography\Model\EnrichedMethod;

/**
 * @template TKey of string
 * @template TValue of EnrichedMethod
 *
 * @template-extends MutableMap<TKey, TValue>
 */
final class EnrichedMethods extends MutableMap
{
}
