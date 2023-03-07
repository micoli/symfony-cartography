<?php

declare(strict_types=1);

namespace App\UserInterface\EventSubscriber;

use App\UserInterface\Twig\SourceCodeExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SourceCodeExtension $twigExtension,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'registerCurrentController',
        ];
    }

    public function registerCurrentController(ControllerEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->twigExtension->setController($event->getController());
        }
    }
}
