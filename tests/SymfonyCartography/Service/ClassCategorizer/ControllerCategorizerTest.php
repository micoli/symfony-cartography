<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Service\ClassCategorizer;

use App\Tests\TestApplication\AbstractTestIntegration;
use App\UserInterface\Controller\Admin\BlogController;
use App\UserInterface\Controller\UserController;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\Categorizer\ControllerCategorizer;
use Micoli\SymfonyCartography\Service\Categorizer\MessengerCommandCategorizer;

/**
 * @internal
 */
class ControllerCategorizerTest extends AbstractTestIntegration
{
    private MessengerCommandCategorizer $messengerCommandCategorizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controllerCategorizer = self::getService(ControllerCategorizer::class);
    }

    public function testIfItCategorizeAsASimpleController(): void
    {
        $enrichedClass = new EnrichedClass('fake.php', BlogController::class, dirname(BlogController::class), []);
        self::assertTrue($this->controllerCategorizer->support($enrichedClass, self::getAnalyzedCodeBase()));
        $this->controllerCategorizer->categorize($enrichedClass);
        self::assertEqualsCanonicalizing(ClassCategory::controller->getValue(), $enrichedClass->getCategory()->getValue());
        self::assertEqualsCanonicalizing(['routes' => ['POST:/{_locale}/admin/post/{id}/delete']], iterator_to_array($enrichedClass->getAttributes()));
    }

    public function testIfItCategorizeAsAnMulitpleRouteController(): void
    {
        $analyzed = self::getAnalyzedCodeBase();
        $enrichedClass = new EnrichedClass('fake.php', UserController::class, dirname(UserController::class), []);
        self::assertTrue($this->controllerCategorizer->support($enrichedClass, $analyzed));
        $this->controllerCategorizer->categorize($enrichedClass);
        self::assertEqualsCanonicalizing(ClassCategory::controller->getValue(), $enrichedClass->getCategory()->getValue());
        self::assertEqualsCanonicalizing(['routes' => [
            'GET:/{_locale}/profile/change-password',
            'POST:/{_locale}/profile/change-password',
        ]], iterator_to_array($enrichedClass->getAttributes()));
    }
}
