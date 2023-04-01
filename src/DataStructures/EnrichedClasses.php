<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use IteratorAggregate;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Ramsey\Collection\Map\AbstractTypedMap;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @extends AbstractTypedMap<string, EnrichedClass>
 *
 * @implements IteratorAggregate<string, EnrichedClass>
 */
final class EnrichedClasses extends AbstractTypedMap implements IteratorAggregate
{
    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return EnrichedClass::class;
    }

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
    #[Ignore]
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
