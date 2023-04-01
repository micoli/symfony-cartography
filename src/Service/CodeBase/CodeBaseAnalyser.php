<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use IteratorAggregate;
use Micoli\SymfonyCartography\DataStructures\ClassInterfaces;
use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\DataStructures\ExtensionResultStore;
use Micoli\SymfonyCartography\DataStructures\InterfaceImplements;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategorizer;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\PsalmRunner;
use Micoli\SymfonyCartography\Service\Filters\ClassesFilter\ClassesFilter;
use Micoli\SymfonyCartography\Service\Filters\MethodCallFilter\MethodCallFilter;
use Psr\Log\LoggerInterface;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Stopwatch\Stopwatch;

final class CodeBaseAnalyser
{
    public const ANALYZED_CODEBASE_CACHE_KEY = 'analyzed_codebase';
    private Finder $finder;
    private FilesystemAdapter $cache;
    private Stopwatch $clock;

    /** @var IteratorAggregate<CodeBaseAnalyzerInterface> */
    private iterable $analyzers;
    /** @var IteratorAggregate<CodeBaseWireInterface> */
    private iterable $wirers;

    /**
     * @param IteratorAggregate<CodeBaseAnalyzerInterface> $analyzers
     * @param IteratorAggregate<CodeBaseWireInterface> $wirers
     * @param list<string> $srcRoots
     */
    public function __construct(
        private readonly ClassParser $classParser,
        private readonly PsalmRunner $psalmRunner,
        private readonly ClassCategorizer $classCategorizer,
        private readonly ClassesFilter $classesFilter,
        private readonly MethodCallFilter $methodCallFilter,
        private readonly LoggerInterface $logger,
        #[TaggedIterator(CodeBaseAnalyzerInterface::class)]
        iterable $analyzers,
        #[TaggedIterator(CodeBaseWireInterface::class)]
        iterable $wirers,
        string $cacheDir,
        private readonly array $srcRoots,
    ) {
        $this->finder = new Finder();
        $this->cache = new FilesystemAdapter('symfony-cartography', 0, $cacheDir);
        $this->clock = new Stopwatch(true);
        $this->analyzers = $analyzers;
        $this->wirers = $wirers;
    }

    public function analyse(bool $forceRefresh = false): AnalyzedCodeBase
    {
        [$timestamp, $files] = $this->findFiles($this->srcRoots);
        $analyzedCodebase = $this->cachedAnalyzeClasses($forceRefresh, $timestamp, $files);

        $this->applyExtensions($analyzedCodebase);

        return $analyzedCodebase;
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    private function applyExtensions(
        AnalyzedCodeBase $analyzedCodebase,
    ): void {
        foreach ($this->analyzers as $analyzer) {
            $analyzedCodebase->extensionResultStore[$analyzer::class] = $analyzer->analyze($analyzedCodebase);
        }

        $this->methodCallFilter->filter($analyzedCodebase);
        $this->classCategorizer->categorizeClasses($analyzedCodebase);
        $this->classesFilter->filterClasses($analyzedCodebase);

        foreach ($this->wirers as $wirer) {
            $wirer->wire($analyzedCodebase);
        }
    }

    /**
     * @param list<string> $root
     *
     * @return array{0:int, 1:array<string, string>}
     */
    public function findFiles(array $root): array
    {
        $this->finder
            ->files()
            ->in($root);

        $timestamp = 0;
        $files = [];
        foreach ($this->finder as $file) {
            $files[$file->getRealPath()] = $file->getRealPath();
            $timestamp = max($timestamp, $file->getMTime());
        }

        return [$timestamp, $files];
    }

    /**
     * @param array<string,string> $files
     *
     * @throws FilesystemException
     */
    public function analyzeClasses(array $files): AnalyzedCodeBase
    {
        $this->logger->info(sprintf('Doing analyze'));
        $classes = new EnrichedClasses();
        foreach ($files as $filename) {
            foreach ($this->classParser->parseFile($filename) as $item) {
                $classes->put($item->namespacedName, $item);
            }
        }
        $this->psalmRunner->analyzeFiles($this->srcRoots, $files, $classes);
        $interfaceImplements = new InterfaceImplements();
        $classInterfaces = new ClassInterfaces();
        foreach ($classes as $class) {
            foreach ($class->interfaces as $interface) {
                $interfaceImplements->addImplements($interface, $class->namespacedName);
                $classInterfaces->addInterface($class->namespacedName, $interface);
            }
        }

        return new AnalyzedCodeBase(
            $classes,
            $interfaceImplements,
            $classInterfaces,
            new ExtensionResultStore(),
        );
    }

    /**
     * @param array<string,string> $files
     */
    private function cachedAnalyzeClasses(bool $force, int $timestamp, array $files): AnalyzedCodeBase
    {
        $this->logger->info(sprintf('Timestamp: %s, Force: %s', $timestamp, $force ? 'true' : 'false'));
        $this->clock->start('analyze');

        $item = $this->cache->getItem(self::ANALYZED_CODEBASE_CACHE_KEY);
        if (!$force && $item->isHit()) {
            /** @var array{timestamp: int, value:AnalyzedCodeBase } $cacheItem */
            $cacheItem = $item->get();
            if ($cacheItem['timestamp'] === $timestamp) {
                $this->logger->info('AnalyzeClasses in cache');

                return $cacheItem['value'];
            }
        }

        $analyzedCodebase = $this->analyzeClasses($files);
        $item->set([
            'timestamp' => $timestamp,
            'value' => $analyzedCodebase,
        ]);
        $this->cache->save($item);

        $this->logger->info((string) $this->clock->stop('analyze'));

        return $analyzedCodebase;
    }
}
