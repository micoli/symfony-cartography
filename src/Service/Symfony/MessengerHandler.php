<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Symfony;

use JsonSerializable;
use Micoli\SymfonyCartography\Model\MethodName;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'never')]
final class MessengerHandler implements JsonSerializable
{
    /**
     * @param class-string $eventClassname
     */
    public function __construct(
        public readonly string $eventClassname,
        public readonly MethodName $handler,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s@%s', $this->eventClassname, (string) $this->handler);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'eventClassname' => $this->eventClassname,
            'handler' => $this->handler->jsonSerialize(),
        ];
    }
}
