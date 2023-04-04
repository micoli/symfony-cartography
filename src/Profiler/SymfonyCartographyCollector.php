<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Profiler;

use Micoli\SymfonyCartography\Model\MethodName;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\VarDumper\Cloner\Data;
use Throwable;

/**
 * @property array{
 *     controllers: list<MethodName>,
 *     enabled: bool
 *  } $data
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SymfonyCartographyCollector extends AbstractDataCollector implements EventSubscriberInterface
{
    private ?ReflectionMethod $parseControllerMethod;
    private ?RequestDataCollector $requestDataCollector;

    public function __construct(
        private readonly bool $enabled,
    ) {
        $this->requestDataCollector = new RequestDataCollector(null);
        $reflectionClass = new ReflectionClass($this->requestDataCollector);
        $this->parseControllerMethod = $reflectionClass->getMethod('parseController');
        $this->parseControllerMethod->setAccessible(true);

        $this->data['controllers'] = [];
    }

    public static function getTemplate(): ?string
    {
        return '@SymfonyCartography/profiler.html.twig';
    }

    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
        $this->data['enabled'] = $this->enabled;
    }

    public function getData(): array|Data
    {
        return $this->data;
    }

    public function isEnabled(): bool
    {
        return $this->data['enabled'];
    }

    /** @return list<MethodName> */
    public function getControllers(): array
    {
        return $this->data['controllers'] ?? [];
    }

    /** @return list<class-string> */
    public function getControllerClassNames(): array
    {
        return array_map(fn (MethodName $method) => $method->namespacedName, $this->data['controllers'] ?? []);
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }
        $this->data['controllers'][] = $this->parseController($event->getController());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    private function parseController(callable $controller): MethodName
    {
        /**
         * @psalm-suppress PossiblyNullReference
         *
         * @var array{class: class-string, method: ?string} $parsedController
         */
        $parsedController = $this->parseControllerMethod->invoke($this->requestDataCollector, $controller);

        return new MethodName(
            $parsedController['class'],
            $parsedController['method'] === null ? '__invoke' : $parsedController['method'],
        );
    }
}
