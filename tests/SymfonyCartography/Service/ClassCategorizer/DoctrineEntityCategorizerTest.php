<?php

declare(strict_types=1);

namespace App\Tests\SymfonyCartography\Service\ClassCategorizer;

use App\Domain\Entity\User;
use App\Tests\TestApplication\AbstractTestIntegration;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\Categorizer\DoctrineEntityCategorizer;

/**
 * @internal
 */
class DoctrineEntityCategorizerTest extends AbstractTestIntegration
{
    private DoctrineEntityCategorizer $doctrineEntityCategorizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->doctrineEntityCategorizer = self::getService(DoctrineEntityCategorizer::class);
    }

    public function testIfItCategorizeAsADoctrineEntity(): void
    {
        $enrichedClass = new EnrichedClass('fake.php', User::class, dirname(User::class), []);
        self::assertTrue($this->doctrineEntityCategorizer->support($enrichedClass, self::getAnalyzedCodeBase()));
        $this->doctrineEntityCategorizer->categorize($enrichedClass);
        self::assertEqualsCanonicalizing(ClassCategory::doctrineEntity->getValue(), $enrichedClass->getCategory()->getValue());
        self::assertEqualsCanonicalizing([
            'tableName' => 'symfony_demo_user',
            'columns' => ['id', 'full_name', 'username', 'email', 'roles', 'password'],
            'identifier' => ['id'],
        ], iterator_to_array($enrichedClass->getAttributes()));
        self::assertEqualsCanonicalizing('Table: symfony_demo_user', $enrichedClass->getCommentsAsString());
    }
}
