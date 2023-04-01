<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\MethodName;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

final class ControllerCategorizer implements ClassCategorizerInterface
{
    /** @var array<string,array{methodName: MethodName,path: string,methods: string[]}[]> */
    private array $controllers = [];

    public function __construct(
        private readonly RouterInterface $router,
    ) {
        $this->controllers = array_reduce(
            iterator_to_array($this->router->getRouteCollection()->getIterator()),
            /** @param array<string,array{methodName: MethodName,path: string,methods: string[]}[]> $controllers */
            function (array $controllers, ?Route $route) {
                if ($route === null) {
                    return $controllers;
                }
                /** @var ?string $controller */
                $controller = $route->getDefault('_controller');
                if ($controller === null) {
                    return $controllers;
                }
                $methodName = MethodName::fromNamespacedMethod($controller);
                if (!array_key_exists($methodName->namespacedName, $this->controllers)) {
                    $controllers[$methodName->namespacedName] = [];
                }
                $controllers[$methodName->namespacedName][] = [
                    'methodName' => $methodName,
                    'path' => $route->getPath(),
                    'methods' => $route->getMethods(),
                ];

                return $controllers;
            },
            [],
        );
    }

    public function support(EnrichedClass $enrichedClass, AnalyzedCodeBase $analyzedCodeBase): bool
    {
        return array_key_exists($enrichedClass->namespacedName, $this->controllers);
    }

    public function categorize(EnrichedClass $enrichedClass): void
    {
        $routes = [];
        foreach ($this->controllers[$enrichedClass->namespacedName] as $route) {
            $enrichedClass->addComment(sprintf(
                '%s (%s)',
                $route['path'],
                implode(',', $route['methods']),
            ));
            foreach ($route['methods'] as $method) {
                $routes[] = sprintf('%s:%s', $method, $route['path']);
            }
        }
        $enrichedClass->addAttribute('routes', $routes);
        $enrichedClass->setCategory(ClassCategory::controller);
    }
}
