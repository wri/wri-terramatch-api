<?php

namespace App\Console\Commands\Traits;

/**
 * Used only in this file to override the default behavior of abort / assert and allow (in some cases) the script
 * to continue after an assertion failure or abort call so that all the errors for a process can be collected and
 * reported together. This override of the abort process is only used when parsing and checking the data from the
 * input CSV in order to provide a report of all errors on the input data without requiring a tedious back and forth
 * process of fixing one error only to uncover the next one.
 */
class AbortException extends \Exception
{
    public ExceptionLevel $level;

    public int $exitCode;

    public function __construct(ExceptionLevel $level, string $message, int $exitCode = -1)
    {
        parent::__construct($message);
        $this->level = $level;
        $this->exitCode = $exitCode;
    }
}
