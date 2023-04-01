<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Symfony\Component\Console\Command\Command;

final class SymfonyConsoleCommandCategorizer extends AbstractClassesBasedCategorizer
{
    /**
     * @var list<class-string>
     */
    protected array $parentsClass = [Command::class];
    protected ClassCategoryInterface $category = ClassCategory::symfonyConsoleCommand;
}
