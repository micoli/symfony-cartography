<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\TwigExtension;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @psalm-type ManifestEntry = array{
 *   file: string,
 *   css: string[],
 *   imports: string[]
 * }
 */
#[AutoconfigureTag('twig.extension')]
final class ViteAssetExtension extends AbstractExtension
{
    /** @psalm-var array<string, ManifestEntry> $manifestData */
    private ?array $manifestData = null;
    private const CACHE_KEY = 'vite_manifest';
    private CacheItemPoolInterface $cache;

    public function __construct(
        private readonly bool $isDev,
        private readonly string $manifest,
        private readonly Environment $twig,
    ) {
        $this->cache = new FilesystemAdapter();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_asset', [$this, 'asset'], ['is_safe' => ['html']]),
        ];
    }

    public function asset(string $entry, array $deps): string
    {
        if ($this->isDev) {
            return $this->assetDev($entry, $deps);
        }

        return $this->assetProd($entry);
    }

    public function assetDev(string $entry, array $deps): string
    {
        $assetsPathDev = 'http://127.0.0.1:5173/assets/';

        return $this->twig->createTemplate(
            <<<TEMPLATE
                    <script type="module" src="{{assetsPathDev}}@vite/client"></script>
                    {% if withReact %}
                    <script type="module">
                        import RefreshRuntime from "{{assetsPathDev}}@react-refresh"
                        RefreshRuntime.injectIntoGlobalHook(window)
                        window.\$RefreshReg\$ = () => {}
                        window.\$RefreshSig\$ = () => (type) => type
                        window.__vite_plugin_react_preamble_installed__ = true
                    </script>
                    {% endif %}
                    <script type="module" src="{{assetsPathDev}}{{entry}}" defer></script>
                TEMPLATE
        )->render([
            'assetsPathDev' => $assetsPathDev,
            'entry' => $entry,
            'withReact' => in_array('react', $deps, true),
        ]);
    }

    public function assetProd(string $entry): string
    {
        $assetsPathProd = '/assets/';
        if ($this->manifestData === null) {
            $item = $this->cache->getItem(self::CACHE_KEY);
            if ($item->isHit()) {
                /** @psalm-var array<string, ManifestEntry> $this->manifestData */
                $this->manifestData = $item->get();
            } else {
                /** @psalm-var array<string, ManifestEntry> $this->manifestData */
                $this->manifestData = json_decode(file_get_contents($this->manifest), true);
                $item->set($this->manifestData);
                $this->cache->save($item);
            }
        }
        $manifestEntry = $this->manifestData[$entry];

        return $this->twig->createTemplate(
            <<<TEMPLATE
                    <script type="module" src="{{ assetsPathProd }}{{ file }}" defer></script>
                    {% for cssFile in css %}
                        <link rel="stylesheet" media="screen" href="{{ assetsPathProd }}{{ cssFile }}"/>
                    {% endfor %}
                    {% for import in imports %}
                        <link rel="modulepreload" href="{{ assetsPathProd }}{{ import }}"/>
                    {% endfor %}                
                TEMPLATE
        )->render([
            'assetsPathProd' => $assetsPathProd,
            'file' => $manifestEntry['file'],
            'css' => $manifestEntry['css'] ?? [],
            'imports' => $manifestEntry['imports'] ?? [],
        ]);
    }
}
