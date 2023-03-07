<?php

declare(strict_types=1);

namespace App\UserInterface\Twig\Components;

use App\Domain\Entity\Post;
use App\Domain\Repository\PostRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
#[AsLiveComponent(name: 'blog_search')]
final class BlogSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {
    }

    /**
     * @return array<Post>
     */
    public function getPosts(): array
    {
        return $this->postRepository->findBySearchQuery($this->query);
    }
}
