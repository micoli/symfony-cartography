<?php

declare(strict_types=1);

namespace App\UserInterface\Twig;

use Closure;
use LogicException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionObject;
use Symfony\Component\String\UnicodeString;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

use function Symfony\Component\String\u;

/**
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class SourceCodeExtension extends AbstractExtension
{
    /**
     * @var callable|null
     */
    private $controller;

    public function setController(?callable $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('show_source_code', [$this, 'showSourceCode'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * @param string|TemplateWrapper $template
     */
    public function showSourceCode(Environment $twig, $template): string
    {
        return $twig->render('debug/source_code.html.twig', [
            'controller' => $this->getController(),
            'views' => $this->getTemplateSource($twig->resolveTemplate($template)),
        ]);
    }

    /**
     * @return array{file_path: string, starting_line: int|false, source_code: string}|null
     */
    private function getController(): ?array
    {
        if ($this->controller === null) {
            return null;
        }

        $method = $this->getCallableReflector($this->controller);

        /** @var string $fileName */
        $fileName = $method->getFileName();

        if (false === $classCode = file($fileName)) {
            throw new LogicException(sprintf('There was an error while trying to read the contents of the "%s" file.', $fileName));
        }

        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();

        while ($startLine > 0) {
            $line = trim($classCode[$startLine - 1]);

            if (\in_array($line, ['{', '}', ''], true)) {
                break;
            }

            --$startLine;
        }

        $controllerCode = implode('', \array_slice($classCode, $startLine, $endLine - $startLine));

        return [
            'file_path' => $fileName,
            'starting_line' => $method->getStartLine(),
            'source_code' => $this->unindentCode($controllerCode),
        ];
    }

    private function getCallableReflector(callable $callable): ReflectionFunctionAbstract
    {
        if (\is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        }

        if (\is_object($callable) && !$callable instanceof Closure) {
            $r = new ReflectionObject($callable);

            return $r->getMethod('__invoke');
        }

        return new ReflectionFunction($callable);
    }

    /**
     * @return array{file_path: string|false, starting_line: int, source_code: string}
     */
    private function getTemplateSource(TemplateWrapper $template): array
    {
        $templateSource = $template->getSourceContext();

        return [
            'file_path' => $templateSource->getPath(),
            'starting_line' => 1,
            'source_code' => $templateSource->getCode(),
        ];
    }

    private function unindentCode(string $code): string
    {
        $codeLines = u($code)->split("\n");

        $indentedOrBlankLines = array_filter($codeLines, static fn (UnicodeString $lineOfCode) => $lineOfCode->isEmpty() || $lineOfCode->startsWith('    '));

        $codeIsIndented = \count($indentedOrBlankLines) === \count($codeLines);
        if ($codeIsIndented) {
            $unindentedLines = array_map(static fn (UnicodeString $lineOfCode) => $lineOfCode->after('    '), $codeLines);
            $code = u("\n")->join($unindentedLines)->toString();
        }

        return $code;
    }
}
