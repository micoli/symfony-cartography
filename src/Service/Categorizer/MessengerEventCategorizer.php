<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use App\Infrastructure\Bus\Message\Event\EventInterface;

final class MessengerEventCategorizer extends AbstractClassesBasedCategorizer
{
    /**
     * @var list<class-string>
     */
    protected array $interfaces = [EventInterface::class];
    protected ClassCategoryInterface $category = ClassCategory::messengerEvent;
}
