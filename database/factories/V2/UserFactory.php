<?php

namespace Database\Factories\V2;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email_address' => $this->generateUniqueEmail(),
            'email_address_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
            'job_role' => 'Manager',
            'phone_number' => $this->faker->phoneNumber(),
            'whatsapp_phone' => $this->faker->phoneNumber(),
            'organisation_id' => Organisation::factory(['status' => Organisation::STATUS_APPROVED])->create()->id,
            'uuid' => $this->faker->uuid(),
        ];
    }

    public function generateUniqueEmail()
    {
        $email = $this->faker->unique()->safeEmail();

        // Check if the generated email is already taken, if yes, regenerate it.
        while (User::where('email_address', $email)->exists()) {
            $email = $this->faker->unique()->safeEmail();
        }

        return $email;
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin',
            ];
        });
    }

    public function terrafundAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'terrafund_admin',
            ];
        });
    }

    public function serviceAccount()
    {
        return $this->state(function (array $attributes) {
            return [
                'api_key' => base64_encode(random_bytes(48)),
                'role' => 'service',
            ];
        });
    }
}
