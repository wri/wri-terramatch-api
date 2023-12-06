<?php

namespace App\Validators\Extensions;

class SustainableDevelopmentGoal extends Extension
{
    public static $name = 'sustainable_development_goal';

    public static $message = [
        'SUSTAINABLE_DEVELOPMENT_GOAL',
        'The {{attribute}} field must contain sustainable development goals.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain sustainable development goals.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $sustainableDevelopmentGoals = array_unique(array_values(config('data.sustainable_development_goals')));

        return in_array($value, $sustainableDevelopmentGoals);
    }
}
