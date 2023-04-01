<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(CodeBaseWireInterface::class)]
interface CodeBaseWireInterface
{
    public function wire(AnalyzedCodeBase $analyzedCodebase): void;
}
