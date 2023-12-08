<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'status' => 'approved',
            'name' => $this->faker->name(),
            'description' => $this->faker->text(200),
            'address_1' => $this->faker->streetAddress(),
            'address_2' => null,
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zip_code' => $this->faker->postcode(),
            'country' => 'GB',
            'phone_number' => $this->faker->phoneNumber(),
            'website' => 'http://www.example.com',
            'avatar' => null,
            'cover_photo' => null,
            'organisation_id' => Organisation::factory()->create()->id,
            'status' => 'approved',
        ];
    }
}
