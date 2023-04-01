<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Symfony\Contracts\EventDispatcher\Event;

final class SymfonyEventCategorizer extends AbstractClassesBasedCategorizer
{
    /**
     * @var list<class-string>
     */
    protected array $parentsClass = [Event::class];
    protected ClassCategoryInterface $category = ClassCategory::symfonyEvent;
}
