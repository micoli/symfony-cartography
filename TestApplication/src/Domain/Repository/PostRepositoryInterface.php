<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Post;
use App\Domain\Entity\Tag;
use App\Domain\Entity\User;
use App\Infrastructure\Pagination\Paginator;

/**
 * @method Post|null findOneByTitle(string $postTitle)
 * @method Post[] findBy(array $criteria,array $orders)
 */
interface PostRepositoryInterface
{
    public function findLatest(int $page = 1, Tag $tag = null): Paginator;

    /**
     * @return Post[]
     */
    public function findBySearchQuery(string $query, int $limit = Paginator::PAGE_SIZE): array;

    public function findByAuthor(User $user): array;
}
