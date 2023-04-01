<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Model;

use JsonSerializable;
use Micoli\SymfonyCartography\DataStructures\EnrichedMethods;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

final class EnrichedClass implements JsonSerializable
{
    /** @var string[] */
    private array $comments = [];

    private EnrichedMethods $methods;
    private ClassCategoryInterface $category = ClassCategory::undefined;
    private ParameterBag $attributes;

    /**
     * @param class-string $namespacedName
     * @param class-string[] $interfaces
     */
    public function __construct(
        public readonly string $filename,
        public readonly string $namespacedName,
        public readonly string $name,
        public readonly array $interfaces,
    ) {
        $this->methods = new EnrichedMethods();
        $this->attributes = new ParameterBag();
    }

    public function addMethod(EnrichedMethod $method): void
    {
        $this->methods->put($method->methodName->name, $method);
    }

    public function addComment(string $comment): void
    {
        $this->comments[] = $comment;
    }

    public function getMethods(): EnrichedMethods
    {
        return $this->methods;
    }

    public function getCategory(): ClassCategoryInterface
    {
        return $this->category;
    }

    public function hasCategory(): bool
    {
        return $this->category !== ClassCategory::undefined;
    }

    public function setCategory(ClassCategoryInterface $category): void
    {
        $this->category = $category;
    }

    public function __toString(): string
    {
        return $this->namespacedName;
    }

    public function jsonSerialize(): array
    {
        return [
            'filename' => $this->filename,
            'namespacedName' => $this->namespacedName,
            'name' => $this->name,
            'methods' => $this->methods->toArray(),
        ];
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function getCommentsAsString(): string
    {
        return implode(PHP_EOL, $this->comments);
    }

    public function getAttributes(): ParameterBag
    {
        return $this->attributes;
    }

    public function addAttribute(string $key, mixed $value): void
    {
        $this->attributes->set($key, $value);
    }
}
