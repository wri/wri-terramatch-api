<?php

namespace Database\Factories\V2\Forms;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $organisation = Organisation::factory()->create();
        $funding = FundingProgramme::first();

        return [
            'uuid' => $this->faker->uuid,
            'organisation_uuid' => $organisation->uuid,
            'funding_programme_uuid' => is_null(data_get($funding, 'uuid')) ? FundingProgramme::factory()->create() : $funding->uuid,
        ];
    }
}
