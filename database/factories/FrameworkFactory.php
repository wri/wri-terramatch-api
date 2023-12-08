<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FrameworkFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        $name = $this->faker->name();

        return [
            'uuid' => $this->faker->uuid,
            'name' => $name,
            'slug' => substr(Str::slug($name), 0, 19),
            'access_code' => strtoupper(substr($this->faker->uuid, 0, 10)),
            'project_form_uuid' => null,
            'project_report_form_uuid' => null,
            'site_form_uuid' => null,
            'site_report_form_uuid' => null,
            'nursery_form_uuid' => null,
            'nursery_report_form_uuid' => null,
        ];
    }
}
