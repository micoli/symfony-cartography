<?php

declare(strict_types=1);

namespace App\UserInterface\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

use function Symfony\Component\String\u;

/**
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
final class RedirectToPreferredLocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @var string[]
     */
    private array $locales;
    private string $defaultLocale;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        string $locales,
        string $defaultLocale = null,
    ) {
        $this->locales = explode('|', trim($locales));

        $this->defaultLocale = $defaultLocale ?: $this->locales[0];

        if (!\in_array($this->defaultLocale, $this->locales, true)) {
            throw new UnexpectedValueException(sprintf('The default locale ("%s") must be one of "%s".', $this->defaultLocale, $locales));
        }

        array_unshift($this->locales, $this->defaultLocale);
        $this->locales = array_unique($this->locales);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || $request->getPathInfo() !== '/') {
            return;
        }
        $referrer = $request->headers->get('referer');
        if ($referrer !== null && u($referrer)->ignoreCase()->startsWith($request->getSchemeAndHttpHost())) {
            return;
        }

        $preferredLanguage = $request->getPreferredLanguage($this->locales);

        if ($preferredLanguage !== $this->defaultLocale) {
            $response = new RedirectResponse($this->urlGenerator->generate('homepage', ['_locale' => $preferredLanguage]));
            $event->setResponse($response);
        }
    }
}
