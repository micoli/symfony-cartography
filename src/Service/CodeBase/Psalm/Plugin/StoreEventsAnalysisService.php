<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase\Psalm\Plugin;

use LogicException;
use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\DataStructures\MethodCallArguments;
use Micoli\SymfonyCartography\DataStructures\MethodCallArgumentUnion;
use Micoli\SymfonyCartography\DataStructures\MethodNames;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodCallArgument;
use Micoli\SymfonyCartography\Model\MethodName;
use PhpParser\Node\Arg;
use Psalm\Internal\Analyzer\MethodAnalyzer;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeAnalysisEvent;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 * @psalm-suppress InternalProperty
 */
#[Autoconfigure(shared: false)]
final class StoreEventsAnalysisService
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private EnrichedClasses $enrichedClasses;

    public function __construct()
    {
    }

    public function init(EnrichedClasses $enrichedClasses): void
    {
        $this->enrichedClasses = $enrichedClasses;
    }

    public function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        $methodCall = $this->getMethodCall($event);
        $callerClass = $this->enrichedClasses->get($methodCall->from->namespacedName);
        if ($callerClass === null) {
            // dump(sprintf('Unknown %s',$methodCall->from));
            return;
        }
        /** @var EnrichedMethod $method */
        $method = $callerClass->getMethods()->get($methodCall->from->name);
        // $method->addMethodCalls(new MethodNames([$methodCall->to]));
        $method->addMethodCalls($methodCall);
    }

    public function afterClassLikeAnalysis(AfterClassLikeAnalysisEvent $event): void
    {
        // todo
        if (str_ends_with($event->getClasslikeStorage()->name, 'Entity\\User')) {
            // dd($event->getClasslikeStorage());
        }
        // $method->addMethodCalls($methodCall);
    }

    private function getMethodCall(AfterMethodCallAnalysisEvent $event): MethodCall
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [$calledNamespacedName, $_] = explode('::', $event->getMethodId(), 2);
        $expr = $event->getExpr();

        $callingMethod = $this->getCallingClassMethod($event);

        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress MixedArgument
         *
         * @psalm-var string $expr->name->name
         */
        $methodName = $expr->name->name;

        return new MethodCall(
            $callingMethod,
            new MethodName(
                $calledNamespacedName,
                $methodName,
            ),
            $this->extractArgs($event),
            null,
        );
    }

    public function getCallingClassMethod(AfterMethodCallAnalysisEvent $event): MethodName
    {
        $n = 0;
        $instance = $event->getStatementsSource();
        while (!($instance instanceof MethodAnalyzer)) {
            $instance = $instance->getSource();
            if ($n++ > 30) {
                throw new LogicException('Too many recursive getSource()');
            }
        }
        /** @var MethodStorage $functionStorage */
        $functionStorage = $instance->getFunctionLikeStorage();

        return new MethodName(
            (string) $functionStorage->defining_fqcln,
            (string) $functionStorage->cased_name,
        );
    }

    private function extractArgs(AfterMethodCallAnalysisEvent $event): MethodCallArguments
    {
        $typeProvider = $event->getStatementsSource()->getNodeTypeProvider();

        return new MethodCallArguments(array_map(
            function (Arg $argument) use ($typeProvider) {
                return new MethodCallArgumentUnion(array_values(array_map(
                    fn (Atomic $atomic) => new MethodCallArgument(
                        str_replace('Psalm\\Type\\Atomic\\', '', $atomic::class),
                        (string) ($atomic->value ?? null),
                    ),
                    $typeProvider->getType($argument->value)?->getAtomicTypes() ?? [],
                )));
            },
            $event->getExpr()->getArgs(),
        ));
    }
}
