<?php

namespace Database\Factories;

use App\Models\Framework;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        $framework = Framework::where('name', '=', 'PPC')
            ->first();
        if (empty($framework)) {
            $framework = Framework::factory()->create([
                'name' => 'PPC',
            ]);
        }

        return [
            'name' => $this->faker->name(),
            'end_date' => $this->faker->dateTimeBetween('-2 years', '-1 day'),
            'country' => 'se',
            'continent' => 'europe',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
            'framework_id' => $framework->id,
            'organisation_id' => Organisation::factory()->create()->id,
        ];
    }
}
