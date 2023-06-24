<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Symfony;

use LogicException;
use Micoli\SymfonyCartography\DataStructures\MessengerHandlers;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyzerInterface;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseWireInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;
use Symfony\Bundle\FrameworkBundle\Command\BuildDebugContainerTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

final class MessengerAnalyser implements CodeBaseAnalyzerInterface, CodeBaseWireInterface
{
    use BuildDebugContainerTrait;

    private ContainerInterface $innerContainer;
    /** @var list<string> */
    private array $dispatcherMethods;

    /** @param list<array{class: class-string, method: string}> $dispatchers */
    public function __construct(
        #[Autowire(service: 'service_container')]
        ContainerInterface $innerContainer,
        private readonly LoggerInterface $logger,
        private readonly SymfonyHelper $symfonyHelper,
        private readonly KernelInterface $kernel,
        private readonly array $dispatchers,
    ) {
        $this->innerContainer = $innerContainer;
        $this->dispatcherMethods = array_map(
            fn (array $method) => sprintf('%s::%s', $method['class'], $method['method']),
            $dispatchers,
        );
    }

    public function analyze(AnalyzedCodeBase $analyzedCodebase): MessengerHandlers
    {
        $container = $this->getContainerBuilder($this->kernel);
        $messageHandlers = new MessengerHandlers();
        foreach ($container->findTaggedServiceIds('messenger.message_handler', true) as $serviceId => $tags) {
            /** @var array{method: string, handles:class-string[]} $tag */
            foreach ($tags as $tag) {
                $serviceClass = $this->symfonyHelper->getServiceClass($container, $serviceId);
                foreach ($this->getHandles($serviceId, $serviceClass, $tag) as $method => $handlers) {
                    foreach ($handlers as $handler) {
                        $messageHandlers->append(new MessengerHandler($handler, new MethodName($serviceClass, $method)));
                    }
                }
            }
        }

        return $messageHandlers;
    }

    /**
     * @param class-string $serviceClass
     * @param array{method: string, handles:class-string[]} $tag
     *
     * @return iterable<string,class-string[]>
     */
    private function getHandles(string $serviceId, string $serviceClass, array $tag): iterable
    {
        if (empty($tag['handles'])) {
            return $this->guessHandledClasses($serviceClass, $serviceId, empty($tag['method']) ? '__invoke' : $tag['method']);
        }
        if (!empty($tag['method'])) {
            /** @psalm-suppress InvalidReturnStatement */
            return [$tag['method'] => $tag['handles']];
        }
        // return [$tag['handles']];
        throw new LogicException(sprintf('Inversed handler/method %s', json_encode($tag['handles'])));
    }

    /**
     * @param class-string $serviceClass
     *
     * @return array<string, list<class-string>>
     */
    private function guessHandledClasses(string $serviceClass, string $serviceId, string $methodName): iterable
    {
        $handlerClass = new ReflectionClass($serviceClass);
        if ($handlerClass->implementsInterface(MessageSubscriberInterface::class)) {
            return $this->getMessageSubscriberInterfaceHandles($handlerClass);
        }

        try {
            $method = $handlerClass->getMethod($methodName);
        } catch (ReflectionException) {
            throw new RuntimeException(sprintf('Invalid handler service "%s": class "%s" must have an "%s()" method.', $serviceId, $handlerClass->getName(), $methodName));
        }

        if ($method->getNumberOfRequiredParameters() === 0) {
            throw new RuntimeException(sprintf('Invalid handler service "%s": method "%s::%s()" requires at least one argument, first one being the message it handles.', $serviceId, $handlerClass->getName(), $methodName));
        }

        $parameters = $method->getParameters();

        /** @var ReflectionNamedType|ReflectionUnionType|null */
        $type = $parameters[0]->getType();

        if (!$type) {
            throw new RuntimeException(sprintf('Invalid handler service "%s": argument "$%s" of method "%s::%s()" must have a type-hint corresponding to the message class it handles.', $serviceId, $parameters[0]->getName(), $handlerClass->getName(), $methodName));
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            $invalidTypes = [];
            foreach ($type->getTypes() as $type) {
                /** @var class-string $classType */
                $classType = (string) $type;
                /** @psalm-suppress PossiblyUndefinedMethod */
                if (!$type->isBuiltin()) {
                    $types[] = $classType;
                } else {
                    $invalidTypes[] = $classType;
                }
            }
            if ($types) {
                return [$methodName => $types];
            }

            throw new RuntimeException(sprintf('Invalid handler service "%s": type-hint of argument "$%s" in method "%s::__invoke()" must be a class , "%s" given.', $serviceId, $parameters[0]->getName(), $handlerClass->getName(), implode('|', $invalidTypes)));
        }

        if ($type->isBuiltin()) {
            throw new RuntimeException(sprintf('Invalid handler service "%s": type-hint of argument "$%s" in method "%s::%s()" must be a class , "%s" given.', $serviceId, $parameters[0]->getName(), $handlerClass->getName(), $methodName, $type->getName()));
        }

        return [$methodName => [$type->getName()]];
    }

    /**
     * @return array<string,list<class-string>>
     */
    private function getMessageSubscriberInterfaceHandles(ReflectionClass $handlerClass): array
    {
        $result = [];
        /**
         * @var class-string<MessageSubscriberInterface> $subscriberClassname
         *
         * @psalm-suppress DeprecatedInterface
         */
        $subscriberClassname = $handlerClass->getName();
        /**
         * @var class-string $event
         * @var array{method: string} $callable
         *
         * @psalm-suppress DeprecatedClass
         */
        foreach ($subscriberClassname::getHandledMessages() as $event => $callable) {
            if (isset($callable['method'])) {
                $result[$callable['method']] = [$event];
            } else {
                throw new LogicException(sprintf('Unhandled MessageSubscriberInterface method %s', json_encode($callable)));
            }
        }

        return $result;
    }

    public function wire(AnalyzedCodeBase $analyzedCodebase): void
    {
        /** @var MessengerHandlers $messengerHandlers */
        $messengerHandlers = $analyzedCodebase->extensionResultStore->offsetGet(MessengerAnalyser::class);

        /**
         * @var EnrichedClass $class
         * @var EnrichedMethod $method
         * @var MethodCall $call
         */
        foreach ($analyzedCodebase->enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            if (!in_array((string) $call->to, $this->dispatcherMethods, true)) {
                continue;
            }
            $method->getMethodCalls()->remove($call);
            $eventClassName = $this->getMessageBusEventArgument($call);
            if ($eventClassName === null) {
                continue;
            }
            foreach ($messengerHandlers->getByEventClassName($eventClassName) as $messengerHandler) {
                $method->getMethodCalls()->append(new MethodCall(
                    $call->from,
                    $messengerHandler->handler,
                    $call->arguments,
                    $eventClassName,
                ));
            }
        }
    }

    /**
     * @return class-string|null
     */
    private function getMessageBusEventArgument(MethodCall $call): ?string
    {
        if ($call->arguments->count() !== 1) {
            return null;
        }
        if ($call->arguments->first()?->count() !== 1) {
            return null;
        }

        /** @psalm-var class-string */
        return $call->arguments->first()?->first()?->value;
    }
}
