<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Symfony;

use LogicException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SymfonyHelper
{
    /**
     * @return class-string
     */
    public function getServiceClass(ContainerBuilder $container, string $serviceId): string
    {
        while (true) {
            $definition = $container->findDefinition($serviceId);

            if (!$definition->getClass() && $definition instanceof ChildDefinition) {
                $serviceId = $definition->getParent();

                continue;
            }

            /** @var ?class-string $class */
            $class = $definition->getClass();
            if ($class === null) {
                throw new LogicException(sprintf('No class found for serviceId "%s"', $serviceId));
            }

            return $class;
        }
    }
}
