<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Controller;

use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[Route('/enrichedClasses')]
final class EnrichedClassesController extends AbstractController
{
    public function __construct(
        private readonly CodeBaseAnalyser $codeParser,
    ) {
    }

    public function __invoke(): Response
    {
        return new JsonResponse(array_map(
            fn (EnrichedClass $class) => [
                'namespacedName' => $class->namespacedName,
                'name' => $class->name,
                'category' => $class->getCategory()->getValue(),
                'methods' => $class->getMethods()->toArray(),
            ],
            array_values($this->codeParser->analyse()->enrichedClasses->toArray()),
        ));
    }
}
