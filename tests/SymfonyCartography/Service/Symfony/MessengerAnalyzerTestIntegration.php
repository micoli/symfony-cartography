<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Service\Symfony;

use App\Tests\TestApplication\AbstractTestIntegration;
use Micoli\SymfonyCartography\DataStructures\MessengerHandlers;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\Symfony\MessengerAnalyser;
use Micoli\SymfonyCartography\Service\Symfony\MessengerHandler;

class MessengerAnalyzerTestIntegration extends AbstractTestIntegration
{
    private MessengerHandlers $messengerHandlers;
    private MessengerAnalyser $messengerAnalyserService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messengerAnalyserService = $this->getService(MessengerAnalyser::class);
        $this->messengerHandlers = $this->messengerAnalyserService->analyze(self::getAnalyzedCodeBase());
    }

    public function testIfHandlersAreWellAnalyzed(): void
    {
        $handlersAsString = array_map(
            fn (MessengerHandler $handler) => (string) $handler,
            $this->messengerHandlers->toArray(),
        );
        self::assertEqualsCanonicalizing([
            'App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Listener\PostCreatedListener::__invoke',
            'App\Domain\Bus\Command\CreateActionCommand@App\Domain\Bus\Subscriber\CreateActionHandler::__invoke',
            'App\Domain\Bus\Event\UserCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriber::__invoke',
            'App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriber::__invoke',
            'App\Domain\Bus\Event\UserPasswordUpdatedEvent@App\Domain\Bus\Subscriber\EventsSubscriber::__invoke',
            'App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onUserCreatedEvent',
            'App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onPostCreatedEvent',
            'App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\PostCreatedSubscriber::__invoke',
            'App\Domain\Bus\Event\PostCreatedEvent@App\Domain\Bus\Subscriber\PostSubscriber::handler',
            'Symfony\Component\Mailer\Messenger\SendEmailMessage@Symfony\Component\Mailer\Messenger\MessageHandler::__invoke',
        ], $handlersAsString);
    }

    public function testIfWiresAreWellAnalyzed(): void
    {
        $eventCalls = [];
        $analyzedCodeBase = self::getAnalyzedCodeBase();
        foreach ($analyzedCodeBase->enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            if (!$analyzedCodeBase->enrichedClasses->offsetExists($call->to->namespacedName)) {
                continue;
            }
            $callToEnrichedClass = $analyzedCodeBase->enrichedClasses[$call->to->namespacedName];
            if (in_array($callToEnrichedClass->getCategory()->asText(), [ClassCategory::messengerEventListener->asText(), ClassCategory::messengerCommandHandler->asText()], true)) {
                $eventCalls[] = sprintf('%s -> %s', $call->from, $call->to);
            }
        }
        self::assertEqualsCanonicalizing([
            'App\UserInterface\Controller\UserController::changePassword -> App\Domain\Bus\Subscriber\EventsSubscriber::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Listener\PostCreatedListener::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\EventsSubscriber::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onUserCreatedEvent',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onPostCreatedEvent',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\PostCreatedSubscriber::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\PostSubscriber::handler',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Listener\PostCreatedListener::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\EventsSubscriber::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onUserCreatedEvent',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::onPostCreatedEvent',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\PostCreatedSubscriber::__invoke',
            'App\UserInterface\Controller\Admin\BlogController::new -> App\Domain\Bus\Subscriber\PostSubscriber::handler',
            'App\Domain\Service\MethodCallerTester::test -> App\Domain\Bus\Subscriber\CreateActionHandler::__invoke',
        ], $eventCalls);
    }
}
