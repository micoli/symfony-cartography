<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
final class IndexController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('@SymfonyCartography/index.html.twig', []);
    }
}
