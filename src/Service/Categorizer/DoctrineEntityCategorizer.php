<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;

/**
 * @psalm-type EntityList = array<class-string, array{
 *      tableName:string,
 *      columns:list<string>,
 *      identifier:list<string>
 *  }>
 */
final class DoctrineEntityCategorizer implements ClassCategorizerInterface
{
    /**
     * @psalm-var EntityList
     */
    private array $entityClasses;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityClasses = array_reduce(
            $entityManager->getMetadataFactory()->getAllMetadata(),
            /** @psalm-param EntityList $accumulator */
            function (array $accumulator, ClassMetadata $classMetadata) {
                /** @var class-string $entityClassName */
                $entityClassName = $classMetadata->getName();
                $accumulator[$entityClassName] = [
                    'tableName' => $classMetadata->getTableName(),
                    'columns' => $classMetadata->getColumnNames(),
                    'identifier' => $classMetadata->getIdentifier(),
                ];

                return $accumulator;
            },
            [],
        );
    }

    public function support(EnrichedClass $enrichedClass, AnalyzedCodeBase $analyzedCodeBase): bool
    {
        return array_key_exists($enrichedClass->namespacedName, $this->entityClasses);
    }

    public function categorize(EnrichedClass $enrichedClass): void
    {
        $entityClass = $this->entityClasses[$enrichedClass->namespacedName];
        foreach ($entityClass as $property => $value) {
            $enrichedClass->addAttribute($property, $value);
        }
        $enrichedClass->addComment(sprintf('Table: %s', $entityClass['tableName']));
        $enrichedClass->setCategory(ClassCategory::doctrineEntity);
    }
}
