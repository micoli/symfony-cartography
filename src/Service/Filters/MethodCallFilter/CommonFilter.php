<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters\MethodCallFilter;

use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\Filters\RuleMatcher;

final class CommonFilter implements MethodCallFilterInterface
{
    /**
     * @param list<string> $rules
     */
    public function __construct(
        private readonly bool $excludeLoopbackCall = true,
        private readonly array $rules = [],
    ) {
    }

    public function isFiltered(MethodName $callerMethod, MethodName $calledMethod): bool
    {
        if ($this->excludeLoopbackCall) {
            if ($calledMethod->namespacedName === $callerMethod->namespacedName) {
                return true;
            }
        }

        return RuleMatcher::isFiltered($this->rules, $calledMethod->namespacedName);
    }
}
