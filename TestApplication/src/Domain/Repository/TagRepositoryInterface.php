<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Tag;

interface TagRepositoryInterface
{
    public function findOneByName(string $name): ?Tag;

    /**
     * @return list<Tag>
     */
    public function findByName(array $names): array;

    /**
     * @return list<Tag>
     */
    public function findAll(): array;

    /**
     * @return list<Tag>
     *
     * @psalm-suppress MissingParamType
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null);
}
