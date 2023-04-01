<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Micoli\SymfonyCartography\DataStructures\MessengerHandlers;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\ArrayMatcher;
use Micoli\SymfonyCartography\Service\Symfony\MessengerAnalyser;

abstract class AbstractMessengerHandlerCategorizer implements ClassCategorizerInterface
{
    /**
     * @var list<class-string>
     */
    protected array $interfaces = [];
    protected ClassCategoryInterface $classCategory = ClassCategory::undefined;

    public function support(EnrichedClass $enrichedClass, AnalyzedCodeBase $analyzedCodeBase): bool
    {
        /** @var MessengerHandlers $messengerHandlers */
        $messengerHandlers = $analyzedCodeBase->extensionResultStore->offsetGet(MessengerAnalyser::class);
        if (!$messengerHandlers->isHandlerByClassName($enrichedClass->namespacedName)) {
            return false;
        }
        foreach ($enrichedClass->getMethods() as $method) {
            foreach ($method->arguments as $argumentTypes) {
                foreach ($argumentTypes as $argumentType) {
                    $argumentInterfaces = $analyzedCodeBase->classInterfaces->get($argumentType);
                    if ($argumentInterfaces === null) {
                        continue;
                    }
                    if (ArrayMatcher::inArray($argumentInterfaces->toArray(), $this->interfaces)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function categorize(EnrichedClass $enrichedClass): void
    {
        $enrichedClass->setCategory($this->classCategory);
    }
}
