<?php

use Illuminate\Support\Str;

function explode_pop(string $delimiter, string $string): string
{
    $parts = explode($delimiter, $string);

    return array_pop($parts);
}

/**
 * This function takes an exception's trace and returns the controller and
 * action which threw it (as an array where the first element is the controller
 * and the second is the action). If a controller and action cannot be found
 * then an array of nulls is returned.
 *
 * Rather than returning after the first match all matches are recorded and only
 * the last match is returned. This means if one controller calls another (which
 * does happen with drafts) the controller and action invoked by the HTTP kernel
 * are returned.
 */
function get_controller_and_action_from_trace(array $stack): array
{
    $matches = [];
    foreach ($stack as $trace) {
        if (key_exists('class', $trace) && key_exists('function', $trace)) {
            if (Str::startsWith($trace['class'], 'App\\Http\\Controllers\\')) {
                $classParts = explode('\\', $trace['class']);
                $matches[] = [array_pop($classParts), $trace['function']];
            }
        }
    }
    if (count($matches) > 0) {
        return array_pop($matches);
    } else {
        return [null, null];
    }
}
