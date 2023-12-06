<?php

namespace Database\Factories\V2\Forms;

use App\Models\V2\Forms\FormOptionList;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormOptionListOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $label = $this->faker->words(3, true);

        return [
            'slug' => Str::slug($label),
            'alt_value' => null,
            'label' => $label,
            'form_option_list_id' => FormOptionList::factory()->create(),
        ];
    }
}
