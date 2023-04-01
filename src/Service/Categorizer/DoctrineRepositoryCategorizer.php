<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

final class DoctrineRepositoryCategorizer extends AbstractClassesBasedCategorizer
{
    /**
     * @var list<class-string>
     */
    protected array $interfaces = [ServiceEntityRepositoryInterface::class];
    protected ClassCategoryInterface $category = ClassCategory::doctrineRepository;
}
