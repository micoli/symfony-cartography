<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use App\Infrastructure\Bus\Message\Command\CommandInterface;

final class MessengerCommandCategorizer extends AbstractClassesBasedCategorizer
{
    /**
     * @var list<class-string>
     */
    protected array $interfaces = [CommandInterface::class];
    protected ClassCategoryInterface $category = ClassCategory::messengerCommand;
}
