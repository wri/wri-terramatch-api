<?php

namespace Database\Factories\V2\UpdateRequests;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class UpdateRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $frameworks = ['ppc', 'terrafund'];
        $frameworkKey = $this->faker->randomElement($frameworks);
        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'framework_key' => $frameworkKey,
            'organisation_id' => $organisation->id,
        ]);

        return [
            'framework_key' => $frameworkKey,
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'status' => $this->faker->randomElement(array_keys(UpdateRequest::$statuses)),
            'updaterequestable_type' => Project::class,
            'updaterequestable_id' => $project->id,
            'content' => json_encode(['name' => 'test project']),
            'feedback' => $this->faker->text(200),
        ];
    }
}
