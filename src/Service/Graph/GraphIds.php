<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

class GraphIds
{
    /** @var list<string> */
    private array $ids = [];

    /**
     * @psalm-suppress FalsableReturnStatement
     * @psalm-suppress InvalidFalsableReturnType
     */
    public function get(string $identifier): int
    {
        if (!in_array($identifier, $this->ids, true)) {
            $this->ids[] = $identifier;
        }

        return array_search($identifier, $this->ids);
    }
}
