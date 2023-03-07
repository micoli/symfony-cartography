<?php

declare(strict_types=1);

namespace App\Tests\TestApplication\Command;

use App\Tests\TestApplication\AbstractTestCommandIntegration;
use App\UserInterface\Command\ListUsersCommand;
use Generator;

/**
 * @internal
 */
class ListUsersTestCommand extends AbstractTestCommandIntegration
{
    /**
     * @dataProvider maxResultsProvider
     *
     * This test verifies the amount of data is right according to the given parameter max results.
     */
    public function testListUsers(int $maxResults): void
    {
        $tester = $this->executeCommand(
            ['--max-results' => $maxResults],
        );

        $emptyDisplayLines = 5;
        self::assertSame($emptyDisplayLines + $maxResults, \mb_substr_count($tester->getDisplay(), "\n"));
    }

    public function maxResultsProvider(): Generator
    {
        yield [1];
        yield [2];
    }

    public function testItSendsNoEmailByDefault(): void
    {
        $this->executeCommand([]);

        $this->assertEmailCount(0);
    }

    public function testItSendsAnEmailIfOptionProvided(): void
    {
        $this->executeCommand(['--send-to' => 'john.doe@symfony.com']);

        $this->assertEmailCount(1);
    }

    protected function getCommandFqcn(): string
    {
        return ListUsersCommand::class;
    }
}
