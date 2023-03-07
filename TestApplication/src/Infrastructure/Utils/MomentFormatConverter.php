<?php

declare(strict_types=1);

namespace App\Infrastructure\Utils;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
final class MomentFormatConverter
{
    /**
     * For ICU formats see http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
     * For Moment formats see https://momentjs.com/docs/#/displaying/format/.
     */
    private const FORMAT_CONVERT_RULES = [
        // year
        'yyyy' => 'YYYY', 'yy' => 'YY', 'y' => 'YYYY',
        // day
        'dd' => 'DD', 'd' => 'D',
        // day of week
        'EE' => 'ddd', 'EEEEEE' => 'dd',
        // timezone
        'ZZZZZ' => 'Z', 'ZZZ' => 'ZZ',
        // letter 'T'
        '\'T\'' => 'T',
    ];

    /**
     * Returns associated moment.js format.
     */
    public function convert(string $format): string
    {
        return strtr($format, self::FORMAT_CONVERT_RULES);
    }
}
