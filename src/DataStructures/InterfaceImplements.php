<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Map\MutableMap;

/**
 * @template-extends MutableMap<string, Classes>
 */
final class InterfaceImplements extends MutableMap
{
    /**
     * @param class-string $interface
     * @param class-string $implement
     */
    public function addImplements(string $interface, string $implement): void
    {
        if (!$this->hasKey($interface)) {
            $this->offsetSet($interface, new Classes([]));
        }
        /** @psalm-suppress PossiblyNullReference */
        $this->get($interface)->append($implement);
    }
}
