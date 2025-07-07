<?php

namespace Database\Factories\V2;

use App\Models\V2\FinancialReport;
use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $frameworks = ['ppc', 'terrafund'];

        return [
            'framework_key' => $this->faker->randomElement($frameworks),
            'organisation_id' => Organisation::factory()->create(),
            'due_at' => $this->faker->dateTime,
            'title' => $this->faker->text(30),
            'status' => FinancialReport::STATUS_DUE,
            'completion' => $this->faker->numberBetween(0, 100),
            'year_of_report' => $this->faker->numberBetween(2020, 2024),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'fin_start_month' => $this->faker->numberBetween(1, 12),
        ];
    }

    public function ppc(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'framework_key' => 'ppc',
            ];
        });
    }

    public function terrafund(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'framework_key' => 'terrafund',
            ];
        });
    }

    public function started(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => FinancialReport::STATUS_STARTED,
            ];
        });
    }

    public function submitted(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => FinancialReport::STATUS_SUBMITTED,
                'submitted_at' => $this->faker->dateTime,
            ];
        });
    }
} 