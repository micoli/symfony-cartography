<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters;

use LogicException;

final class RuleMatcher
{
    /**
     * @param list<string> $rules
     */
    public static function isFiltered(array $rules, string $testee): bool
    {
        foreach ($rules as $rule) {
            $prefix = $rule[0];
            if ($prefix === '+') {
                if (!str_starts_with($testee, substr($rule, 1))) {
                    return true;
                }
                continue;
            }
            if ($prefix === '-') {
                if (str_starts_with($testee, substr($rule, 1))) {
                    return true;
                }
                continue;
            }
            throw new LogicException('Common filter rules must start by + ou -');
        }

        return false;
    }
}
