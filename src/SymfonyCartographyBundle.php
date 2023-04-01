<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SymfonyCartographyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
