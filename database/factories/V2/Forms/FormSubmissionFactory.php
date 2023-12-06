<?php

namespace Database\Factories\V2\Forms;

use App\Http\Resources\V2\Forms\AnswersCollection;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        $user = User::factory()->create();

        return [
            'name' => $this->faker->sentence(),
            'form_id' => Form::factory()->create()->uuid,
            'status' => $this->faker->randomElement(array_keys(FormSubmission::$statuses)),
            'answers' => new AnswersCollection([]),
            'user_id' => $user->uuid,
            'organisation_uuid' => $user->organisation->uuid,
            'project_pitch_uuid' => $this->faker->uuid,
        ];
    }
}
