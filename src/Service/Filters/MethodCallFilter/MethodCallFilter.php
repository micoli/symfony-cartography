<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters\MethodCallFilter;

use IteratorAggregate;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class MethodCallFilter
{
    /** @var IteratorAggregate<MethodCallFilterInterface> */
    private iterable $filters;

    /** @param IteratorAggregate<MethodCallFilterInterface> $filters */
    public function __construct(
        #[TaggedIterator(MethodCallFilterInterface::class)]
        iterable $filters,
    ) {
        $this->filters = $filters;
    }

    private function isFiltered(MethodName $callerMethod, MethodName $calledMethod): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($callerMethod, $calledMethod)) {
                return true;
            }
        }

        return false;
    }

    public function filter(AnalyzedCodeBase $analyzedCodeBase): void
    {
        /**
         * @var EnrichedClass $enrichedClass
         * @var EnrichedMethod $method
         * @var MethodCall $methodCall
         **/
        foreach ($analyzedCodeBase->enrichedClasses->getMethodCalls() as [$enrichedClass, $method, $methodCall]) {
            if ($this->isFiltered($method->methodName, $methodCall->to)) {
                $method->getMethodCalls()->remove($methodCall);
            }
        }
    }
}
