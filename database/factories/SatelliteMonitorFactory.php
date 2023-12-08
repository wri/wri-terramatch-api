<?php

namespace Database\Factories;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

class SatelliteMonitorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'satellite_monitorable_type' => TerrafundProgramme::class,
            'satellite_monitorable_id' => TerrafundProgramme::factory()->create()->id,
            'alt_text' => $this->faker->text(),
            'map' => Upload::factory()->tiff()->create()->id,

        ];
    }
}
