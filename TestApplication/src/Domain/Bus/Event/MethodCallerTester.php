<?php

declare(strict_types=1);

namespace App\Domain\Bus\Event;

use App\Domain\Bus\Event\TestClasses\Test1;
use App\Domain\Bus\Event\TestClasses\Test2;
use App\Domain\Bus\Event\TestClasses\Test3;
use App\Domain\Bus\Event\TestClasses\Test4;
use App\Domain\Bus\Event\TestClasses\Test5;
use App\Domain\Bus\Event\TestClasses\Test6;
use App\Domain\Bus\Event\TestClasses\Test7;
use App\Domain\Bus\Event\TestClasses\TestStatic1;
use App\Infrastructure\Bus\CommandBusInterface;
use App\Infrastructure\Bus\Message\Event\AsyncEventInterface;
use Exception;

final class MethodCallerTester implements AsyncEventInterface
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @psalm-suppress RedundantCondition
     * @psalm-suppress TypeDoesNotContainType
     * @psalm-suppress InvalidReturnType
     * **/
    public function test(CalledMethodEvent $event, array $testArray): void
    {
        $bool = true;
        $int = random_int(1, 3);
        $test3 = new Test3();
        $test3->callTest4();
        CalledMethodEvent::dispatch(
            $this->createArg(),
            $event,
            1111,
            $testArray,
            $test3,
            new Test4(),
            $bool ? new Test5() : new Test6(),
            TestStatic1::create(),
            match ($int) {
                1 => new Test6(),
                2 => new Test7(),
                default => throw new Exception('Unexpected match value')
            },
        );
    }

    /**
     * @psalm-suppress InvalidReturnType
     **/
    private function createArg(): Test1|Test2|null
    {
    }
}
