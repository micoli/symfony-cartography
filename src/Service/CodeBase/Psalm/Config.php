<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase\Psalm;

use Psalm\Config as PsalmConfig;
use Psalm\Config\ProjectFileFilter;
use Psalm\Internal\IncludeCollector;
use SimpleXMLElement;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 * @psalm-suppress InternalProperty
 */
#[When(env: 'never')]
final class Config extends PsalmConfig
{
    private static ?ProjectFileFilter $cached_project_files = null;

    /**
     * @var string[]
     */
    private array $projectDirectories;

    /**
     * @param string[] $projectDirectories
     *
     * @psalm-suppress ConstructorSignatureMismatch
     */
    public function __construct(
        string $psalmConfig,
        array $projectDirectories,
        string $cacheDir,
    ) {
        parent::__construct();

        foreach ($this->php_extensions as $ext => $_enabled) {
            $this->php_extensions[$ext] = true;
        }
        $this->throw_exception = true;
        $this->use_docblock_types = true;
        $this->level = 1;
        $this->cache_directory = $cacheDir;

        $this->base_dir = getcwd() . DIRECTORY_SEPARATOR;

        if (!self::$cached_project_files) {
            self::$cached_project_files = ProjectFileFilter::loadFromXMLElement(
                new SimpleXMLElement($psalmConfig),
                $this->base_dir,
                true,
            );
        }

        $this->project_files = self::$cached_project_files;
        $this->projectDirectories = $projectDirectories;
        $this->setIncludeCollector(new IncludeCollector());
        $this->collectPredefinedConstants();
        $this->collectPredefinedFunctions();
    }

    public function getComposerFilePathForClassLike(string $fq_classlike_name): bool
    {
        return false;
    }

    public function getProjectDirectories(): array
    {
        return $this->projectDirectories;
    }
}
