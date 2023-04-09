<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service;

final class ClassHelper
{
    private const NAMESPACE_SEPARATOR = '\\';

    public static function extractNamespace(string $namespacedName): string
    {
        $parts = explode(self::NAMESPACE_SEPARATOR, $namespacedName);
        array_pop($parts);

        return implode(self::NAMESPACE_SEPARATOR, $parts);
    }

    public static function extractClassname(string $namespacedName): string
    {
        $parts = explode(self::NAMESPACE_SEPARATOR, $namespacedName);

        return array_pop($parts);
    }
}
