<?php

namespace App\Validators\Extensions;

class RestorationGoal extends Extension
{
    public static $name = 'restoration_goal';

    public static $message = [
        'RESTORATION_GOAL',
        'The {{attribute}} field must contain restoration goals.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain restoration goals.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $restorationGoals = array_unique(array_values(config('data.restoration_goals')));

        return in_array($value, $restorationGoals);
    }
}
