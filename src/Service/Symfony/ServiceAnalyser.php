<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Symfony;

use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\BuildDebugContainerTrait;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\KernelInterface;

final class ServiceAnalyser
{
    use BuildDebugContainerTrait;

    private array $messageHandlers = [];
    private ContainerInterface $container;

    public function __construct(
        #[Autowire(service: 'service_container')]
        ContainerInterface $container,
        private readonly SymfonyHelper $helper,
        private readonly LoggerInterface $logger,
    ) {
        $this->container = $container;
    }

    public function analyze(
        KernelInterface $kernel,
    ): array {
        $container = $this->getContainerBuilder($kernel);

        $result = [];
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            $result[$serviceId] = $definition->getClass();
            /** @var string $definitionClass */
            $definitionClass = $definition->getClass();
            if ($definitionClass == '') {
                // dd($container->resolveServices($definition->getFactory()));
                // todo manage factories
                // dd($definition);
            }
            $this->logger->notice(sprintf('- [%s]', $definitionClass));
            /** @psalm-suppress MixedAssignment */
            foreach ($definition->getArguments() as $argument) {
                if ($argument instanceof Reference) {
                    $this->logger->notice(sprintf('    - %s [%d]', $argument->__toString(), $argument::class));
                    // dd([
                    //    $definition->getClass(),
                    //    $this->helper->getServiceClass($container, $argument->__toString())
                    // ]);
                    continue;
                }
                if ($argument instanceof TaggedIteratorArgument) {
                    // todo tagged argumentds
                    // dd($argument);
                    continue;
                }

                if ($argument instanceof IteratorArgument) {
                    // dd($container->resolveServices($argument->getValues()));
                    // dd($argument->getValues());
                    // todo tagged IteratorArgument
                    // dd($argument);
                    continue;
                }

                if ($argument instanceof AbstractArgument) {
                    // todo tagged AbstractArgument
                    // dd($argument);
                    continue;
                }

                if ($argument instanceof ServiceClosureArgument) {
                    // todo tagged AbstractArgument
                    // dd($argument);
                    continue;
                }

                if (is_object($argument)) {
                    throw new LogicException(sprintf('Unknown service definition class [%s]', $argument::class));
                }
            }
            $this->logger->notice(' ');
        }

        return $result;
    }
}
