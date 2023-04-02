<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Model;

use Micoli\SymfonyCartography\DataStructures\ClassInterfaces;
use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\DataStructures\ExtensionResultStore;
use Micoli\SymfonyCartography\DataStructures\InterfaceImplements;

final class AnalyzedCodeBase
{
    public function __construct(
        public EnrichedClasses $enrichedClasses,
        public InterfaceImplements $interfaceImplements,
        public ClassInterfaces $classInterfaces,
        public ExtensionResultStore $extensionResultStore,
    ) {
    }

    public static function createEmpty(): self
    {
        return new self(
            new EnrichedClasses(),
            new InterfaceImplements(),
            new ClassInterfaces(),
            new ExtensionResultStore(),
        );
    }

    /**
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        $methods = [];
        $methodCallCount = 0;
        foreach ($this->enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            $methods[(string) $method->methodName] = 1;
            ++$methodCallCount;
        }

        return [
            'enrichedClasses' => $this->enrichedClasses->count(),
            'methods' => count($methods),
            'method calls' => $methodCallCount,
            'interfaceImplements' => $this->interfaceImplements->count(),
            'classInterfaces' => $this->classInterfaces->count(),
        ];
    }
}
