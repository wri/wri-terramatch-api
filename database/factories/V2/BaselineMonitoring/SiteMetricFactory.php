<?php

namespace Database\Factories\V2\BaselineMonitoring;

use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteMetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'monitorable_type' => TerrafundSite::class,
            'monitorable_id' => TerrafundSite::factory()->create()->id,

            'tree_count' => $this->faker->randomFloat(2, 0, 10000),
            'tree_cover' => $this->faker->randomFloat(2, 0, 100),

            'field_tree_count' => $this->faker->randomFloat(2, 0, 10000),
        ];
    }
}
