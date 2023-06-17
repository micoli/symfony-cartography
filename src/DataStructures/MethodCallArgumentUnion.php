<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Set\MutableSet;
use Micoli\SymfonyCartography\Model\MethodCallArgument;

/**
 * @template TValue of MethodCallArgument
 *
 * @template-extends MutableSet<TValue>
 */
final class MethodCallArgumentUnion extends MutableSet
{
}
