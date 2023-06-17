<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Map\MutableMap;
use Micoli\Multitude\Set\MutableSet;

/**
 * @template-extends MutableMap<class-string, MutableSet>
 */
final class ExtensionResultStore extends MutableMap
{
}
