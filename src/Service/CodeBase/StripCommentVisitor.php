<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class StripCommentVisitor extends NodeVisitorAbstract
{
    public function beforeTraverse(array $nodes): void
    {
    }

    public function enterNode(Node $node): void
    {
        if ($node->hasAttribute('comments')) {
            $node->setAttribute('comments', '##removed');
        }
    }
}
