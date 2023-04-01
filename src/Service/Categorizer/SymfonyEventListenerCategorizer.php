<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SymfonyEventListenerCategorizer extends AbstractClassesBasedCategorizer
{
    /**
     * @var list<class-string>
     */
    protected array $interfaces = [EventSubscriberInterface::class];
    protected ClassCategoryInterface $category = ClassCategory::symfonyEventListener;
}
