<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use JsonSerializable;
use Micoli\SymfonyCartography\Service\Symfony\MessengerHandler;
use Ramsey\Collection\AbstractSet;

/**
 * @extends AbstractSet<MessengerHandler>
 *
 * @implements IteratorAggregate<MessengerHandler>
 */
final class MessengerHandlers extends AbstractSet implements IteratorAggregate, JsonSerializable
{
    use JsonSerializableTrait;

    public function getType(): string
    {
        return MessengerHandler::class;
    }

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
