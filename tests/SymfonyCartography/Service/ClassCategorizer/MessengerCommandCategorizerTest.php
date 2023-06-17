<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Service\ClassCategorizer;

use App\Domain\Bus\Command\CreateActionCommand;
use App\Infrastructure\Bus\Message\Command\CommandInterface;
use App\Tests\TestApplication\AbstractTestIntegration;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\Categorizer\MessengerCommandCategorizer;

/**
 * @internal
 */
class MessengerCommandCategorizerTest extends AbstractTestIntegration
{
    private MessengerCommandCategorizer $messengerCommandCategorizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messengerCommandCategorizer = self::getService(MessengerCommandCategorizer::class);
    }

    public function testIfItCategorizeAsACommand(): void
    {
        $enrichedClass = new EnrichedClass('fake.php', CreateActionCommand::class, basename(CreateActionCommand::class), [CommandInterface::class], []);
        self::assertTrue($this->messengerCommandCategorizer->support($enrichedClass, self::getAnalyzedCodeBase()));
        $this->messengerCommandCategorizer->categorize($enrichedClass);
        self::assertEqualsCanonicalizing(ClassCategory::messengerCommand->getValue(), $enrichedClass->getCategory()->getValue());
    }
}
