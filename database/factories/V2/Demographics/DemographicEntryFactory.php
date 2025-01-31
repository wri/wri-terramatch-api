<?php

namespace Database\Factories\V2\Demographics;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemographicEntryFactory extends Factory
{
    public const GENDERS = ['male', 'female', 'non-binary', 'unknown'];
    public const AGES = ['youth', 'adult', 'elder', 'unknown'];
    public const ETHNICITIES = ['indigenous', 'other', 'unknown'];

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
            'subtype' => null,
            'name' => $this->faker->randomElement(self::GENDERS),
            'amount' => $this->faker->randomNumber([0, 5000]),
        ];
    }

    public function gender()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DemographicEntry::GENDER,
                'name' => $this->faker->randomElement(self::GENDERS),
            ];
        });
    }

    public function age()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DemographicEntry::AGE,
                'name' => $this->faker->randomElement(self::AGES),
            ];
        });
    }

    public function ethnicity()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DemographicEntry::ETHNICITY,
                'subtype' => $this->faker->randomElement(self::ETHNICITIES),
            ];
        });
    }
}
