<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Service\Symfony;

use App\Tests\TestApplication\AbstractTestIntegration;
use Micoli\SymfonyCartography\DataStructures\MessengerHandlers;
use Micoli\SymfonyCartography\Service\Symfony\MessengerAnalyser;
use Micoli\SymfonyCartography\Service\Symfony\MessengerHandler;

/**
 * @internal
 */
class MessengerAnalyzerTestIntegration extends AbstractTestIntegration
{
    private MessengerHandlers $messengerHandlers;

    protected function setUp(): void
    {
        parent::setUp();
        $messengerAnalyserService = $this->getService(MessengerAnalyser::class);
        $this->messengerHandlers = $messengerAnalyserService->analyze(self::getAnalyzedCodeBase());
    }

    public function testIfHandlersAreWellAnalyzed(): void
    {
        $handlersAsString = array_map(
            fn (MessengerHandler $handler) => (string) $handler,
            $this->messengerHandlers->toArray(),
        );
        self::assertEqualsCanonicalizing([
            "App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Listener\PostCreatedListener::__invoke",
            "App\Domain\Bus\Command\CreateActionCommand@App\Domain\Bus\Subscriber\CreateActionHandler::__invoke",
            "App\Domain\Bus\Event\UserCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriber::__invoke",
            "App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriber::__invoke",
            "App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onUserCreatedEvent",
            "App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onPostCreatedEvent",
            "App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\PostCreatedSubscriber::__invoke",
            "App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\PostSubscriber::handler",
            "Symfony\Component\Mailer\Messenger\SendEmailMessage@Symfony\Component\Mailer\Messenger\MessageHandler::__invoke",
        ], $handlersAsString);
    }
}
