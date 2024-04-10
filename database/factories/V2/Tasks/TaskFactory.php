<?php

namespace Database\Factories\V2\Tasks;

use App\Models\V2\Organisation;
use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fakedDate = $this->faker->dateTimeBetween('-3 months', '+3 months');
        $date = Carbon::create($fakedDate);

        return [
            'status' => array_keys(Task::$statuses)[0],
            'organisation_id' => (Organisation::factory()->create())->id,
            'project_id' => (Organisation::factory()->create())->id,
            'period_key' => $date->year . '-' . $date->month,
            'due_at' => $date,
        ];
    }
}
