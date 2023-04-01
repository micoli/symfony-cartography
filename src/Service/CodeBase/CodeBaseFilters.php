<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;

class CodeBaseFilters
{
    public function filterFrom(EnrichedClasses $enrichedClasses, string $className): void
    {
        $connectedTo = [];
        $connectedFrom = [];
        /**
         * @var EnrichedClass $class
         * @var MethodName $method
         * @var MethodCall $call
         */
        foreach ($enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            if (!array_key_exists($call->from->namespacedName, $connectedTo)) {
                $connectedTo[$call->from->namespacedName] = [];
            }
            $connectedTo[$call->from->namespacedName][] = $call->to->namespacedName;

            if (!array_key_exists($call->to->namespacedName, $connectedFrom)) {
                $connectedFrom[$call->to->namespacedName] = [];
            }
            $connectedFrom[$call->to->namespacedName][] = $call->from->namespacedName;
        }
        $filtered = $this->getNextCall($connectedTo, $className, []) + $this->getNextCall($connectedFrom, $className, []);
        foreach ($enrichedClasses as $i => $class) {
            if (!array_key_exists($class->namespacedName, $filtered)) {
                $enrichedClasses->remove($i);
            }
        }
    }

    public function filterOrphans(EnrichedClasses $enrichedClasses): void
    {
        $connectedCalls = [];
        /**
         * @var EnrichedClass $class
         * @var MethodName $method
         * @var MethodCall $call
         */
        foreach ($enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            $connectedCalls[$call->to->namespacedName] = 1;
            $connectedCalls[$call->from->namespacedName] = 1;
        }
        foreach ($enrichedClasses as $index => $class) {
            if (!array_key_exists($class->namespacedName, $connectedCalls)) {
                $enrichedClasses->remove($index);
            }
        }
    }

    /**
     * @param array<string,array<string>> $connectedTo
     * @param array<string,int> $list
     *
     * @return array<string,int> $list
     */
    private function getNextCall(array $connectedTo, string $classFrom, array $list): array
    {
        if (isset($list[$classFrom])) {
            return $list;
        }
        $list[$classFrom] = 1;
        if (!isset($connectedTo[$classFrom])) {
            return $list;
        }
        foreach ($connectedTo[$classFrom] as $call) {
            $list = array_merge($list, $this->getNextCall($connectedTo, $call, $list));
        }

        return $list;
    }
}
