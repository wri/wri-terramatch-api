<?php

namespace Database\Factories\V2;

use App\Models\V2\ImpactStory;
use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ImpactStoryFactory extends Factory
{
    protected $model = ImpactStory::class;

    public function definition()
    {
        return [
          'uuid' => (string) Str::uuid(),

            'title' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['draft', 'published']),
            'organization_id' => Organisation::factory(),
            'date' => $this->faker->date,
            'category' => $this->faker->word,
            'thumbnail' => $this->faker->imageUrl,
            'content' => $this->faker->paragraph,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
