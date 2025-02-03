<?php

namespace Database\Factories\V2\Demographics;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemographicEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'demographic_id' => Demographic::factory()->create()->id,
            'type' => 'gender',
            'subtype' => $this->faker->randomElement(DemographicEntry::GENDERS),
            'name' => null,
            'amount' => $this->faker->randomNumber([0, 5000]),
        ];
    }

    public function gender()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DemographicEntry::GENDER,
                'subtype' => $this->faker->randomElement(DemographicEntry::GENDERS),
            ];
        });
    }

    public function age()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DemographicEntry::AGE,
                'subtype' => $this->faker->randomElement(DemographicEntry::AGES),
            ];
        });
    }

    public function ethnicity()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DemographicEntry::ETHNICITY,
                'subtype' => $this->faker->randomElement(DemographicEntry::ETHNICITIES),
            ];
        });
    }
}
