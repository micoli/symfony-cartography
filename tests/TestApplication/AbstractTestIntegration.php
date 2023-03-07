<?php

declare(strict_types=1);

namespace App\Tests\TestApplication;

use App\Tests\T;
use Exception;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webmozart\Assert\Assert;

abstract class AbstractTestIntegration extends KernelTestCase
{
    // use MockeryPHPUnitIntegration;

    protected static $initialized = false;

    protected function setUp(): void
    {
        self::bootKernel();

        if (!self::$initialized) {
            self::getService(CodeBaseAnalyser::class)->clearCache();
            self::$initialized = true;
        }
    }

    public static function getAnalyzedCodeBase(): AnalyzedCodeBase
    {
        $codebaseAnalyzer = self::getService(CodeBaseAnalyser::class);

        return $codebaseAnalyzer->analyse();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // $this->closeMockery();
    }

    /**
     * @psalm-template T
     *
     * @psalm-param class-string<T> $serviceName
     *
     * @psalm-return T
     *
     * @throws Exception
     */
    protected static function getService(string $serviceName): object
    {
        $service = static::getContainer()->get($serviceName);
        Assert::notNull($service, sprintf('Service with reference "%s" not found', $serviceName));

        return $service;
    }

    protected function getParameter(string $parameterName): mixed
    {
        return static::getContainer()->getParameter($parameterName);
    }

    protected function setService(string $serviceId, object $service): void
    {
        static::getContainer()->set($serviceId, $service);
    }
}
