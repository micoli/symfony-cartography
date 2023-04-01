<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DataStructures;

use Ramsey\Collection\AbstractSet;

trait JsonSerializableTrait
{
    public function jsonSerialize(): mixed
    {
        if ($this instanceof AbstractSet) {
            /**
             * @psalm-suppress RedundantFunctionCall
             */
            return array_values($this->toArray());
        }

        /**
         * @psalm-suppress MixedMethodCall
         */
        return $this->toArray();
    }
}
