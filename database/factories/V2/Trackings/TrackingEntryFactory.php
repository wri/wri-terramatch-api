<?php

namespace Database\Factories\V2\Trackings;

use App\Models\V2\Trackings\Tracking;
use App\Models\V2\Trackings\TrackingEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tracking_id' => Tracking::factory()->create()->id,
            'type' => 'gender',
            'subtype' => $this->faker->randomElement(TrackingEntry::GENDERS),
            'name' => null,
            'amount' => $this->faker->randomNumber([0, 5000]),
        ];
    }

    public function gender()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TrackingEntry::GENDER,
                'subtype' => $this->faker->randomElement(TrackingEntry::GENDERS),
            ];
        });
    }

    public function age()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TrackingEntry::AGE,
                'subtype' => $this->faker->randomElement(TrackingEntry::AGES),
            ];
        });
    }

    public function ethnicity()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TrackingEntry::ETHNICITY,
                'subtype' => $this->faker->randomElement(TrackingEntry::ETHNICITIES),
            ];
        });
    }
}
