<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Micoli\Multitude\Map\MutableMap;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @template-extends MutableMap<class-string, EnrichedClass>
 */
final class EnrichedClasses extends MutableMap
{
    #[Ignore]
    public function getImplements(string $interface): iterable
    {
        /**
         * @var class-string $className
         * @var EnrichedClass $enrichedClass
         */
        foreach (self::getIterator() as $className => $enrichedClass) {
            if (in_array($interface, $enrichedClass->interfaces)) {
                yield $className => $enrichedClass;
            }
        }
    }

    /**
     * @return iterable<array{
     *     0: EnrichedClass,
     *     1: EnrichedMethod,
     *     2: MethodCall
     * }>
     */
    public function getMethodCalls(): iterable
    {
        foreach ($this as $class) {
            foreach ($class->getMethods() as $method) {
                foreach ($method->getMethodCalls() as $call) {
                    yield [$class, $method, $call];
                }
            }
        }
    }
}
