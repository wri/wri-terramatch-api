<?php

namespace Database\Factories\V2\Workdays;

use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkdayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $gender = ['female', 'male', 'gender-undefined'];
        $age = ['youth-15-24', 'adult-24-65', 'elder-65+', 'age-undefined'];
        $ethnicity = ['middle-eastern', 'hispanic', 'irish', 'native-american', 'Jewish', 'pacific-islander', 'ethnicity-undefined'];


        return [
            'workdayable_type' => SiteReport::class,
            'workdayable_id' => SiteReport::factory()->create(),
            'amount' => $this->faker->numberBetween(0, 5000),
            'collection' => $this->faker->randomElement(Workday::$siteCollections),
            'gender' => $this->faker->randomElement($gender),
            'age' => $this->faker->randomElement($age),
            'ethnicity' => $this->faker->randomElement($ethnicity),
        ];
    }
}
