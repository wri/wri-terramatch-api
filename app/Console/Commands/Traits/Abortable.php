<?php

namespace App\Console\Commands\Traits;

use JetBrains\PhpStorm\NoReturn;

trait Abortable
{
    #[NoReturn]
    protected function abort(string $message, int $exitCode = 1): void
    {
        echo $message;
        exit($exitCode);
    }

    protected function assert(bool $condition, string $message, int $exitCode = 1): void
    {
        if (! $condition) {
            $this->abort($message, $exitCode);
        }
    }
}
