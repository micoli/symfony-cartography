<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodName;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Safe;
use Safe\Exceptions\FilesystemException;

final class ClassParser
{
    private NodeTraverser $traverser;
    private NodeFinder $nodeFinder;
    private Parser $parser;

    public function __construct()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NodeVisitor\ParentConnectingVisitor());
        $this->traverser->addVisitor(new NodeVisitor\NameResolver(null, ['preserveOriginalNames' => true, 'replaceNodes' => true]));
        $this->traverser->addVisitor(new StripCommentVisitor());
        $this->nodeFinder = new NodeFinder();
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @throws FilesystemException
     */
    public function parseFile(string $absoluteFilePath): EnrichedClasses
    {
        $results = new EnrichedClasses();

        $stmts = $this->parser->parse(Safe\file_get_contents($absoluteFilePath));
        if ($stmts === null) {
            return $results;
        }

        $stmts = $this->traverser->traverse($stmts);
        /** @var Node\Stmt\Class_[] $classes */
        $classes = $this->nodeFinder->findInstanceOf($stmts, Node\Stmt\Class_::class);
        foreach ($classes as $class) {
            /** @var ?class-string $namespacedName */
            $namespacedName = $class->namespacedName?->toString();
            $className = $class->name?->name;
            assert($namespacedName !== null);
            assert($className !== null);
            try {
                $reflectedClass = new ReflectionClass($namespacedName);
            } catch (ReflectionException $exception) {
                throw new ReflectionException(sprintf('Class "%s" does not exist in %s', $namespacedName, $absoluteFilePath), 0, $exception);
            }

            $enrichedClass = new EnrichedClass(
                $absoluteFilePath,
                $namespacedName,
                $className,
                $reflectedClass->getInterfaceNames(),
            );
            $this->addInternalMethods($namespacedName, $enrichedClass, $reflectedClass, $class);
            $this->addExtendedMethods($namespacedName, $enrichedClass, $reflectedClass);
            $results->put($namespacedName, $enrichedClass);
        }

        return $results;
    }

    public function addInternalMethods(string $namespacedName, EnrichedClass $enrichedClass, ReflectionClass $reflectedClass, Node\Stmt\Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            $enrichedClass->addMethod(
                new EnrichedMethod(
                    new MethodName(
                        $namespacedName,
                        $classMethod->name->name,
                    ),
                    $this->getMethodParameters($reflectedClass, $classMethod->name->name),
                    true,
                ),
            );
        }
    }

    /**
     * @param class-string $namespacedName
     *
     * @throws ReflectionException
     */
    public function addExtendedMethods(string $namespacedName, EnrichedClass $enrichedClass, ReflectionClass $reflectedClass): void
    {
        foreach ($reflectedClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED) as $reflectedMethod) {
            if ($enrichedClass->getMethods()->offsetExists($reflectedMethod->name)) {
                continue;
            }
            $enrichedClass->addMethod(
                new EnrichedMethod(
                    new MethodName(
                        $namespacedName,
                        $reflectedMethod->name,
                    ),
                    $this->getMethodParameters($reflectedClass, $reflectedMethod->name),
                    false,
                ),
            );
        }
    }

    /**
     * @return array<int<0, max>, list<class-string>>
     */
    private function getMethodParameters(ReflectionClass $reflectedClass, string $method): array
    {
        $reflectedMethod = $reflectedClass->getMethod($method);
        $result = [];
        foreach ($reflectedMethod->getParameters() as $index => $parameter) {
            /** @var ReflectionNamedType|ReflectionUnionType|null $type */
            $type = $parameter->getType();

            if (!$type) {
                $result[$index] = [];
                continue;
            }

            if ($type instanceof ReflectionUnionType) {
                $types = [];
                foreach ($type->getTypes() as $type) {
                    /** @var class-string $typeClass */
                    $typeClass = (string) $type;
                    /** @psalm-suppress PossiblyUndefinedMethod */
                    if (!$type->isBuiltin()) {
                        $types[] = $typeClass;
                    }
                }
                $result[$index] = $types;
                continue;
            }

            /** @var class-string $typeClass */
            $typeClass = (string) $type;
            $result[$index] = [$typeClass];
        }

        return $result;
    }
}
