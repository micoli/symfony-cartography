<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @class-extends ServiceEntityRepository<Tag>
 */
final class TagRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findOneByName(string $name): ?Tag
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findByName(array $names): array
    {
        return $this->findBy([
            'name' => $names,
        ]);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }
}
