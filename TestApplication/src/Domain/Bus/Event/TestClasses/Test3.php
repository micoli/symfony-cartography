<?php

declare(strict_types=1);

namespace App\Domain\Bus\Event\TestClasses;

final class Test3
{
    public function callTest4(): void
    {
        $test4 = new Test4();
        $test4->callTest5();
    }
}
