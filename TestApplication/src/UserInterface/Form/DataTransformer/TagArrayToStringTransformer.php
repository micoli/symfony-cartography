<?php

declare(strict_types=1);

namespace App\UserInterface\Form\DataTransformer;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\String\AbstractString;

use function Symfony\Component\String\u;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 * @author Jonathan Boyer <contact@grafikart.fr>
 *
 * @template-implements DataTransformerInterface<Tag[], string>
 */
final class TagArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private TagRepositoryInterface $tags,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): string
    {
        if ($value === null) {
            return '';
        }

        return implode(',', $value);
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-param string|null $value
     *
     * @psalm-return list<Tag>
     */
    public function reverseTransform($value): array
    {
        if ($value === null || u($value)->isEmpty()) {
            return [];
        }

        $names = array_filter(array_unique($this->trim(u($value)->split(','))));

        /** @var list<Tag> $tags */
        $tags = $this->tags->findByName($names);
        $newNames = array_diff($names, $tags);
        foreach ($newNames as $name) {
            $tags[] = new Tag($name);
        }

        return $tags;
    }

    /**
     * @param AbstractString[] $strings
     *
     * @return string[]
     */
    private function trim(array $strings): array
    {
        $result = [];

        foreach ($strings as $string) {
            $result[] = trim((string) $string);
        }

        return $result;
    }
}
