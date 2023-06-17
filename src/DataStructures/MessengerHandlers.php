<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Set\MutableSet;
use Micoli\SymfonyCartography\Service\Symfony\MessengerHandler;

/**
 * @template-extends MutableSet<MessengerHandler>
 */
final class MessengerHandlers extends MutableSet
{
    /**
     * @param class-string $eventClassName
     *
     * @return iterable<MessengerHandler>
     */
    public function getByEventClassName(string $eventClassName): iterable
    {
        foreach ($this as $messengerHandler) {
            if ($messengerHandler->eventClassname === $eventClassName) {
                yield $messengerHandler;
            }
        }
    }

    /**
     * @param class-string $handlerClassName
     */
    public function isHandlerByClassName(string $handlerClassName): bool
    {
        foreach ($this as $messengerHandler) {
            if ($messengerHandler->handler->namespacedName === $handlerClassName) {
                return true;
            }
        }

        return false;
    }
}
