<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase\Psalm;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\Plugin\StoreEventsAnalysisService;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\Plugin\StoreEventsListener;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\Plugin\StorePlugin;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\Provider\FakeParserCacheProvider;
use Psalm\Context;
use Psalm\Internal\Analyzer\FileAnalyzer;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Codebase\TaintFlowGraph;
use Psalm\Internal\Provider\FakeFileProvider;
use Psalm\Internal\Provider\Providers;
use Psalm\Internal\RuntimeCaches;

/**
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 * @psalm-suppress InternalProperty
 */
final class PsalmRunner
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private ProjectAnalyzer $projectAnalyzer;

    public function __construct(
        private readonly StoreEventsAnalysisService $afterMethodCallAnalysisService,
        private readonly string $cacheDir,
    ) {
        $this->initEnvironment();

        RuntimeCaches::clearAll();
    }

    public function __destruct()
    {
        unset($this->projectAnalyzer);
        RuntimeCaches::clearAll();
    }

    /**
     * @param list<string> $srcRoots
     * @param array<string,string> $filePaths
     */
    public function analyzeFiles(array $srcRoots, array $filePaths, EnrichedClasses $enrichedClasses): void
    {
        $this->setup($this->cacheDir, $srcRoots);
        $this->afterMethodCallAnalysisService->init($enrichedClasses);
        StoreEventsListener::initialize($this->afterMethodCallAnalysisService);

        $codebase = $this->projectAnalyzer->getCodebase();
        $codebase->taint_flow_graph = new TaintFlowGraph();
        $codebase->addFilesToAnalyze($filePaths);
        $codebase->collectLocations();
        $codebase->scanFiles();
        $codebase->config->visitStubFiles($codebase);

        $context = new Context();

        foreach ($filePaths as $filePath) {
            $file_analyzer = new FileAnalyzer(
                $this->projectAnalyzer,
                $filePath,
                $codebase->config->shortenFileName($filePath),
            );
            $file_analyzer->analyze($context);
        }
    }

    private function initEnvironment(): void
    {
        ini_set('memory_limit', '-1');

        if (!defined('PSALM_VERSION')) {
            define('PSALM_VERSION', '4.0.0');
        }

        if (!defined('PHP_PARSER_VERSION')) {
            define('PHP_PARSER_VERSION', '4.0.0');
        }
    }

    /**
     * @param string[] $srcRoots
     *
     * @throws \Psalm\Exception\ConfigException
     */
    public function setup(string $cacheDir, array $srcRoots): void
    {
        $xmlPaths = array_map(fn (string $path) => sprintf('<directory name="%s" />', $path), $srcRoots);
        $xml = sprintf(
            <<<XML
                <?xml version="1.0"?>
                <psalm
                    errorLevel="5"
                >
                    <projectFiles>
                        %s
                        <ignoreFiles>
                        </ignoreFiles>
                    </projectFiles>
                </psalm>
                XML,
            implode('', $xmlPaths),
        );
        $config = new Config($xml, [], $cacheDir);
        class_exists(StoreEventsListener::class);

        $config->addPluginClass(StorePlugin::class);

        $this->projectAnalyzer = new ProjectAnalyzer(
            $config,
            new Providers(
                new FakeFileProvider(),
                new FakeParserCacheProvider(),
            ),
        );
        $config->initializePlugins($this->projectAnalyzer);

        $this->projectAnalyzer->setPhpVersion('8.2', 'config');
    }
}
