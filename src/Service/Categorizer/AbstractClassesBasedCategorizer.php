<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\ArrayMatcher;
use ReflectionClass;
use ReflectionException;

abstract class AbstractClassesBasedCategorizer implements ClassCategorizerInterface
{
    /**
     * @var list<class-string>
     */
    protected array $interfaces = [];
    /**
     * @var list<class-string>
     */
    protected array $parentsClass = [];
    protected ClassCategoryInterface $category = ClassCategory::undefined;

    /**
     * @throws ReflectionException
     */
    public function support(EnrichedClass $enrichedClass, AnalyzedCodeBase $analyzedCodeBase): bool
    {
        $reflectionClass = new ReflectionClass($enrichedClass->namespacedName);
        if (ArrayMatcher::inArray($reflectionClass->getInterfaceNames(), $this->interfaces)) {
            return true;
        }
        foreach ($this->parentsClass as $parentClass) {
            /** @psalm-suppress RedundantCondition */
            if ($reflectionClass->isSubclassOf($parentClass)) {
                return true;
            }
        }

        return false;
    }

    public function categorize(EnrichedClass $enrichedClass): void
    {
        $enrichedClass->setCategory($this->category);
    }
}
