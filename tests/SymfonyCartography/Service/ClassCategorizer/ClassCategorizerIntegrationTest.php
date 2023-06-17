<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Service\ClassCategorizer;

use App\Tests\TestApplication\AbstractTestIntegration;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategorizer;

/**
 * @internal
 */
class ClassCategorizerIntegrationTest extends AbstractTestIntegration
{
    private ClassCategorizer $classCategorizer;
    private AnalyzedCodeBase $analyzedCodeBase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classCategorizer = $this->getService(ClassCategorizer::class);
        $this->analyzedCodeBase = self::getAnalyzedCodeBase();
    }

    public function testIfHandlersAreWellAnalyzed(): void
    {
        $this->classCategorizer->categorizeClasses($this->analyzedCodeBase);
        $categorizedClasses = array_map(
            fn (EnrichedClass $class) => sprintf('%s::%s', $class->namespacedName, $class->getCategory()->asText()),
            array_values($this->analyzedCodeBase->enrichedClasses->toArray()),
        );
        self::assertEqualsCanonicalizing([
            'App\UserInterface\Form\PostType::undefined',
            'App\UserInterface\Form\DataTransformer\TagArrayToStringTransformer::undefined',
            'App\UserInterface\Form\CommentType::undefined',
            'App\UserInterface\Form\Type\DateTimePickerType::undefined',
            'App\UserInterface\Form\Type\TagsInputType::undefined',
            'App\UserInterface\Form\UserType::undefined',
            'App\UserInterface\Form\ChangePasswordType::undefined',
            'App\UserInterface\Controller\UserController::controller',
            'App\UserInterface\Controller\SecurityController::controller',
            'App\UserInterface\Controller\BlogController::controller',
            'App\UserInterface\Controller\Admin\BlogController::controller',
            'App\UserInterface\Command\AddUserCommand::symfonyConsoleCommand',
            'App\UserInterface\Command\DeleteUserCommand::symfonyConsoleCommand',
            'App\UserInterface\Command\ListUsersCommand::symfonyConsoleCommand',
            'App\UserInterface\Twig\Components\BlogSearchComponent::undefined',
            'App\UserInterface\Twig\AppExtension::undefined',
            'App\UserInterface\Twig\SourceCodeExtension::undefined',
            'App\UserInterface\EventSubscriber\RedirectToPreferredLocaleSubscriber::symfonyEventListener',
            'App\UserInterface\EventSubscriber\ControllerSubscriber::symfonyEventListener',
            'App\Infrastructure\Pagination\Paginator::undefined',
            'App\Infrastructure\Repository\TagRepository::doctrineRepository',
            'App\Infrastructure\Repository\PostRepository::doctrineRepository',
            'App\Infrastructure\Repository\UserRepository::doctrineRepository',
            'App\Infrastructure\Bus\MessengerEventDispatcher::undefined',
            'App\Infrastructure\Bus\MessengerCommandBus::undefined',
            'App\Infrastructure\Utils\Validator::undefined',
            'App\Infrastructure\Utils\MomentFormatConverter::undefined',
            'App\Infrastructure\EventSubscriber\CheckRequirementsSubscriber::symfonyEventListener',
            'App\Domain\Security\PostVoter::undefined',
            'App\Domain\Entity\Post::doctrineEntity',
            'App\Domain\Entity\Tag::doctrineEntity',
            'App\Domain\Entity\User::doctrineEntity',
            'App\Domain\Entity\Comment::doctrineEntity',
            'App\Domain\Bus\Listener\PostCreatedListener::messengerEventListener',
            'App\Domain\Bus\Command\CreateActionCommand::messengerCommand',
            'App\Domain\Bus\Subscriber\EventsSubscriber::messengerEventListener',
            'App\Domain\Bus\Subscriber\CreateActionHandler::messengerCommandHandler',
            'App\Domain\Bus\Subscriber\EventsSubscriberOldSchool::messengerEventListener',
            'App\Domain\Bus\Subscriber\PostSubscriber::messengerEventListener',
            'App\Domain\Bus\Subscriber\PostCreatedSubscriber::messengerEventListener',
            'App\Domain\Bus\Event\PostCreatedEvent::messengerEvent',
            'App\Domain\Bus\Event\UserPasswordUpdatedEvent::messengerEvent',
            'App\Domain\Bus\Event\CalledMethodEvent::messengerEvent',
            'App\Domain\Bus\Event\TestClasses\TestStatic1::undefined',
            'App\Domain\Bus\Event\TestClasses\Test6::undefined',
            'App\Domain\Bus\Event\TestClasses\Test7::undefined',
            'App\Domain\Bus\Event\TestClasses\Test5::undefined',
            'App\Domain\Bus\Event\TestClasses\Test4::undefined',
            'App\Domain\Bus\Event\TestClasses\Test1::undefined',
            'App\Domain\Bus\Event\TestClasses\Test3::undefined',
            'App\Domain\Bus\Event\TestClasses\Test2::undefined',
            'App\Domain\Bus\Event\UserCreatedEvent::messengerEvent',
            'App\Domain\Service\MethodCallerTester::messengerEvent',
            'App\Domain\EventSubscriber\CommentNotificationSubscriber::symfonyEventListener',
            'App\Domain\Event\CommentCreatedEvent::symfonyEvent',
            'App\Kernel::undefined',
            'App\Domain\Specification\User\UserSpecification::undefined',
            'App\Domain\Specification\User\UserSpecificationDTO::undefined',
            'App\Domain\Specification\User\UserSpecificationIsUnique::undefined',
            'App\Domain\Specification\User\UserSpecificationNonEmptyPassword::undefined',
        ], $categorizedClasses);
    }
}
