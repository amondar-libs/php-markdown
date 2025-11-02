<?php

declare(strict_types = 1);

namespace Tests;

use Closure;
use JetBrains\PhpStorm\NoReturn;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    #[NoReturn]
    final public function benchmark(Closure $callback): never
    {
        $start = hrtime(true);

        $callback();

        $elapsed = (hrtime(true) - $start) / 1e6;

        fwrite(STDOUT, sprintf("time=%.3f ms\n", $elapsed));

        exit(0);
    }
}
