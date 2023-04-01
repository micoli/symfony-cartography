<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters\MethodCallFilter;

use Micoli\SymfonyCartography\Model\MethodName;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(MethodCallFilterInterface::class)]
interface MethodCallFilterInterface
{
    public function isFiltered(MethodName $callerMethod, MethodName $calledMethod): bool;
}
