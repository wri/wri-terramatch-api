<?php

namespace App\Console\Commands\Traits;

trait Abortable
{
    protected function executeAbortableScript(callable $execute): void
    {
        try {
            $execute();
        } catch (AbortException $e) {
            $this->logException($e);
            exit($e->exitCode);
        }
    }

    protected function logException(AbortException $e): void
    {
        switch ($e->level) {
            case ExceptionLevel::Warning:
                $this->warn($e->getMessage());

                break;

            case ExceptionLevel::Error:
                $this->error($e->getMessage());

                break;
        }
    }

    /**
     * @throws AbortException
     */
    protected function abort(string $message, ExceptionLevel $level = ExceptionLevel::Error, $exitCode = 1): void
    {
        throw new AbortException($level, $message, $exitCode);
    }

    /**
     * @throws AbortException
     */
    protected function assert(
        bool $condition,
        string $message,
        ExceptionLevel $level = ExceptionLevel::Error,
        int $exitCode = 1
    ): void {
        if (! $condition) {
            $this->abort($message, $level, $exitCode);
        }
    }
}
