<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase\Psalm\Plugin;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class StorePlugin implements PluginEntryPointInterface
{
    public function __construct()
    {
    }

    public function __invoke(RegistrationInterface $registration, SimpleXMLElement|null $config = null): void
    {
        $registration->registerHooksFromClass(StoreEventsListener::class);
    }
}
