<?php

namespace Database\Factories\V2\Projects;

use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectInviteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'project_id' => Project::factory()->create()->id,
            'email_address' => $this->faker->unique()->safeEmail(),
            'token' => Str::random(64),
        ];
    }
}
