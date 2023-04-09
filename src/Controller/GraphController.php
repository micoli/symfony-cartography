<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Controller;

use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
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
        private readonly GraphGeneratorInterface $graphGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var string $classNamesParameter */
        $classNamesParameter = $request->query->get('classNames', '');

        return new Response(
            $this->graphGenerator->html(explode(',', $classNamesParameter)),
            200,
        );
    }
}
