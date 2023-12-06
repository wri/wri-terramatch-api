<?php

namespace App\Helpers;

class JsonPatchHelper
{
    /**
     * This method reorders remove ops. This is done in order to prevent
     * remove ops referencing indexes that have already been removed! For
     * example:
     *
     * - You have a 0 indexed array like so ["foo", "bar", "baz"]
     * - You issue a remove op for indexes 1 and 2
     * - After index 1 gets removed the array is reindexed
     * - There is now no index 2 due to the reindexing
     * - Thus the remove op for index 2 fails
     *
     * However, if you run the remove ops in reverse order the remove ops work
     * just fine. That is what this method does.
     */
    public static function reorderRemoveOps(array $ops): array
    {
        $groups = [];
        foreach ($ops as $position => $op) {
            if ($op->op != 'remove' || ! preg_match('/\\/[0-9]+/', $op->path)) {
                continue;
            }
            $index = explode_pop('/', $op->path);

            $end = (strlen($index) + 1) * -1;
            $group = substr($op->path, 0, $end);
            if (! array_key_exists($group, $groups)) {
                $groups[$group] = [];
            }
            $groups[$group][] = (object) [
                'position' => $position,
                'index' => (int) $index,
            ];
        }
        $reordered = $ops;
        foreach ($groups as $group) {
            usort($group, function ($a, $b) {
                return $a->index < $b->index ? 1 : ($a->index == $b->index ? 0 : -1);
            });
            $i = 0;
            $j = count($group) - 1;
            while ($j > -1) {
                $from = $group[$i]->position;
                $to = $group[$j]->position;
                $reordered[$to] = $ops[$from];
                $i += 1;
                $j -= 1;
            }
        }

        return $reordered;
    }

    private function __construct()
    {
    }
}
