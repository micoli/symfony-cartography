<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Controller;

use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseFilters;
use Micoli\SymfonyCartography\Service\Graph\PlantUmlGraphGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[Route('/graph', 'cartography_graph')]
final class GraphController extends AbstractController
{
    public function __construct(
        private readonly CodeBaseAnalyser $codeParser,
        private readonly CodeBaseFilters $codeBaseFilters,
        private readonly PlantUmlGraphGenerator $plantUmlGraphGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $enrichedClasses = $this->codeParser->analyse()->enrichedClasses;
        $this->codeBaseFilters->filterOrphans($enrichedClasses);
        /** @var ?string $className */
        $className = $request->query->get('className');
        if ($className) {
            $this->codeBaseFilters->filterFrom($enrichedClasses, $className);
        }

        return new Response(
            $this->plantUmlGraphGenerator->svg($enrichedClasses),
            200,
            ['Content-Type' => 'image/svg+xml'],
        );
    }
}
