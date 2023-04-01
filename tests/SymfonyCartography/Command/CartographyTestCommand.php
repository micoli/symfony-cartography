<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Command;

use App\Tests\TestApplication\AbstractTestCommandIntegration;
use Micoli\SymfonyCartography\Command\CartographyCommand;

/**
 * @internal
 */
class CartographyTestCommand extends AbstractTestCommandIntegration
{
    public function testCommand(): void
    {
        $tester = $this->executeCommand(['--force']);
        self::assertSame(0, $tester->getStatusCode());
    }

    protected function getCommandFqcn(): string
    {
        return CartographyCommand::class;
    }
}
