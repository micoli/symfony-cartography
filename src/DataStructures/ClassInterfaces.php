<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Map\MutableMap;

/**
 * @template-extends MutableMap<class-string, Classes>
 */
final class ClassInterfaces extends MutableMap
{
    /**
     * @param class-string $class
     * @param class-string $interface
     */
    public function addInterface(string $class, string $interface): void
    {
        if (!$this->hasKey($class)) {
            $this->set($class, new Classes([]));
        }
        /** @psalm-suppress PossiblyNullReference */
        $this->get($class)->append($interface);
    }
}
