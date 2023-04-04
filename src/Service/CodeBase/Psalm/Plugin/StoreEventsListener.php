<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase\Psalm\Plugin;

use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;

final class StoreEventsListener implements AfterMethodCallAnalysisInterface
{
    private static ?StoreEventsAnalysisService $service;

    public static function initialize(StoreEventsAnalysisService $service): void
    {
        self::$service = $service;
    }

    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        assert(self::$service !== null);
        self::$service->afterMethodCallAnalysis($event);
    }
}
