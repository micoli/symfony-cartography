<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Profiler;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseFilters;
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
 *     controllers: list<class-string>,
 *     statistics:array<string, int>
 *  } $data
 */
final class SymfonyCartographyCollector extends AbstractDataCollector implements EventSubscriberInterface
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     *
     * @var list<array{request: Request, controller: callable}>
     */
    private array $controllers = [];
    private AnalyzedCodeBase $analyzedCodeBase;
    private ?ReflectionMethod $parseControllerMethod;
    private ?RequestDataCollector $requestDataCollector;

    public function __construct(
        private readonly CodeBaseAnalyser $codeParser,
        private readonly CodeBaseFilters $codeBaseFilters,
    ) {
        $this->analyzedCodeBase = $this->codeParser->analyse();
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
        foreach ($this->analyzedCodeBase->enrichedClasses as $enrichedClass) {
            if ($enrichedClass->getCategory()->getValue() !== ClassCategory::controller->getValue()) {
                continue;
            }
            foreach ($this->controllers as $controller) {
                if ($this->parseController($controller['controller'])->namespacedName === $enrichedClass->namespacedName) {
                    if (!in_array($enrichedClass->namespacedName, $this->data['controllers'], true)) {
                        $this->data['controllers'][] = $enrichedClass->namespacedName;
                    }
                }
            }
        }
        if (count($this->data['controllers']) === 1) {
            $this->codeBaseFilters->filterOrphans($this->analyzedCodeBase->enrichedClasses);
            $this->codeBaseFilters->filterFrom($this->analyzedCodeBase->enrichedClasses, $this->data['controllers'][0]);
            $statistics = [];
            foreach ($this->analyzedCodeBase->enrichedClasses as $enrichedClass) {
                $category = $enrichedClass->getCategory()->asText();
                $count = array_key_exists($category, $statistics) ? $statistics[$category] : 0;
                $statistics[$category] = $count + 1;
            }
            $this->data['statistics'] = $statistics;
        }
    }

    public function getData(): array|Data
    {
        return $this->data;
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

    /** @return list<class-string> */
    public function getFilteredControllers(): array
    {
        return $this->data['controllers'] ?? [];
    }

    /** @return array<string, int> */
    public function getStatistics(): array
    {
        return $this->data['statistics'] ?? [];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $this->controllers[] = [
            'request' => $event->getRequest(),
            'controller' => $event->getController(),
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
