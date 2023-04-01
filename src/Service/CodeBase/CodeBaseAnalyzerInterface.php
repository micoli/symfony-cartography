<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Ramsey\Collection\AbstractCollection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(CodeBaseAnalyzerInterface::class)]
interface CodeBaseAnalyzerInterface
{
    public function analyze(AnalyzedCodeBase $analyzedCodebase): AbstractCollection;
}
