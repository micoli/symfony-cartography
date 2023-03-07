<?php

declare(strict_types=1);

namespace App\Tests\TestApplication\Form\DataTransformer;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use App\UserInterface\Form\DataTransformer\TagArrayToStringTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class TagArrayToStringTransformerTest extends TestCase
{
    public function testCreateTheRightAmountOfTags(): void
    {
        $tags = $this->getMockedTransformer()->reverseTransform('Hello, Demo, How');

        self::assertCount(3, $tags);
        self::assertSame('Hello', $tags[0]->getName());
    }

    public function testCreateTheRightAmountOfTagsWithTooManyCommas(): void
    {
        $transformer = $this->getMockedTransformer();

        self::assertCount(3, $transformer->reverseTransform('Hello, Demo,, How'));
        self::assertCount(3, $transformer->reverseTransform('Hello, Demo, How,'));
    }

    public function testTrimNames(): void
    {
        $tags = $this->getMockedTransformer()->reverseTransform('   Hello   ');

        self::assertSame('Hello', $tags[0]->getName());
    }

    public function testDuplicateNames(): void
    {
        $tags = $this->getMockedTransformer()->reverseTransform('Hello, Hello, Hello');

        self::assertCount(1, $tags);
    }

    public function testUsesAlreadyDefinedTags(): void
    {
        $persistedTags = [
            new Tag('Hello'),
            new Tag('World'),
        ];
        $tags = $this->getMockedTransformer($persistedTags)->reverseTransform('Hello, World, How, Are, You');

        self::assertCount(5, $tags);
        self::assertSame($persistedTags[0]->jsonSerialize(), $tags[0]->jsonSerialize());
        self::assertSame($persistedTags[1]->jsonSerialize(), $tags[1]->jsonSerialize());
    }

    public function testTransform(): void
    {
        $persistedTags = [
            new Tag('Hello'),
            new Tag('World'),
        ];
        $transformed = $this->getMockedTransformer()->transform($persistedTags);

        self::assertSame('Hello,World', $transformed);
    }

    /**
     * @param array<int, object> $findByReturnValues The values returned when calling to the findBy() method
     */
    private function getMockedTransformer(array $findByReturnValues = []): TagArrayToStringTransformer
    {
        $tagRepository = $this->getMockBuilder(TagRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tagRepository->expects(self::any())
            ->method('findBy')
            ->willReturn($findByReturnValues);

        return new TagArrayToStringTransformer($tagRepository);
    }
}
