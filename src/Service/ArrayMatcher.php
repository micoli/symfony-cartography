<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service;

final class ArrayMatcher
{
    /**
     * @param string[] $needles
     * @param string[] $haystack
     */
    public static function inArray(array $needles, array $haystack): bool
    {
        foreach ($needles as $needle) {
            if (in_array($needle, $haystack)) {
                return true;
            }
        }

        return false;
    }

    public static function pregMatch(string $string, string ...$patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $suffixes
     */
    public static function strEndWith(string $string, array $suffixes): bool
    {
        foreach ($suffixes as $suffix) {
            if (\str_ends_with($string, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
