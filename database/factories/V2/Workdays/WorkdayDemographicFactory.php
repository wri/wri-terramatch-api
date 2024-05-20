<?php

namespace Database\Factories\V2\Workdays;

use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkdayDemographicFactory extends Factory
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
            'workday_id' => Workday::factory()->create()->id,
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
                'type' => WorkdayDemographic::GENDER,
                'name' => $this->faker->randomElement(self::GENDERS),
            ];
        });
    }

    public function age()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => WorkdayDemographic::AGE,
                'name' => $this->faker->randomElement(self::AGES),
            ];
        });
    }

    public function ethnicity()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => WorkdayDemographic::ETHNICITY,
                'subtype' => $this->faker->randomElement(self::ETHNICITIES),
            ];
        });
    }
}
