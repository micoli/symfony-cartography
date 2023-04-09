<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Controller;

use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseFilters;
use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[Route('/graph_data', 'cartography_graph_data')]
final class GraphDataController extends AbstractController
{
    public function __construct(
        private readonly CodeBaseAnalyser $codeParser,
        private readonly CodeBaseFilters $codeBaseFilters,
        private readonly GraphGeneratorInterface $graphGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var string $classNamesParameter */
        $classNamesParameter = $request->query->get('classNames', '');

        $enrichedClasses = $this->codeParser->analyse()->enrichedClasses;
        $this->codeBaseFilters->filterOrphans($enrichedClasses);

        $this->codeBaseFilters->filterFrom($enrichedClasses, explode(',', $classNamesParameter));

        return new JsonResponse($this->graphGenerator->data($enrichedClasses));
    }
}
