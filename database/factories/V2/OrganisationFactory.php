<?php

namespace Database\Factories\V2;

use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(array_keys(Organisation::$statuses)),
            'type' => $this->faker->randomElement(array_keys(Organisation::$types)),

            'private' => false,
            'name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'hq_street_1' => $this->faker->streetAddress(),
            'hq_street_2' => $this->faker->streetAddress(),
            'hq_city' => $this->faker->city(),
            'hq_state' => $this->faker->state(),
            'hq_zipcode' => $this->faker->postcode(),
            'hq_country' => $this->faker->randomElement(array_keys(config('wri.countries'))),

            'founding_date' => $this->faker->date('Y-m-d', 'now'),
            'description' => $this->faker->text(500),

            'countries' => $this->faker->randomElements(array_keys(config('wri.countries')), $this->faker->numberBetween(1, 4)),
            'languages' => $this->faker->randomElements(['english', 'french', 'spanish', 'portugese'], $this->faker->numberBetween(1, 3)),

            'web_url' => $this->faker->url(),
            'facebook_url' => $this->faker->url(),
            'instagram_url' => $this->faker->url(),
            'linkedin_url' => $this->faker->url(),
            'twitter_url' => $this->faker->url(),

            'fin_start_month' => $this->faker->numberBetween(1, 12),
            'fin_budget_3year' => $this->faker->randomFloat(2, 0, 999999999999),
            'fin_budget_2year' => $this->faker->randomFloat(2, 0, 999999999999),
            'fin_budget_1year' => $this->faker->randomFloat(2, 0, 999999999999),
            'fin_budget_current_year' => $this->faker->randomFloat(2, 0, 999999999999),

            'ha_restored_total' => $this->faker->randomFloat(2, 0, 10000),
            'ha_restored_3year' => $this->faker->randomFloat(2, 0, 10000),
            'relevant_experience_years' => $this->faker->numberBetween(0, 150),

            'trees_grown_total' => $this->faker->numberBetween(0, 1000000),
            'trees_grown_3year' => $this->faker->numberBetween(0, 1000000),
            'tree_care_approach' => $this->faker->text(500),

            'ft_permanent_employees' => $this->faker->numberBetween(0, 1000000),
            'pt_permanent_employees' => $this->faker->numberBetween(0, 1000000),
            'temp_employees' => $this->faker->numberBetween(0, 1000000),
            'female_employees' => $this->faker->numberBetween(0, 1000000),
            'male_employees' => $this->faker->numberBetween(0, 1000000),
            'young_employees' => $this->faker->numberBetween(0, 1000000),
            'over_35_employees' => $this->faker->numberBetween(0, 1000000),
            'additional_funding_details' => $this->faker->text(500),
            'community_experience' => $this->faker->text(500),
            'business_model' => $this->faker->text(50),
            'subtype' => $this->faker->text(50),
            'organisation_revenue_this_year' => $this->faker->numberBetween(0, 1000000),
            'total_engaged_community_members_3yr' => $this->faker->numberBetween(0, 1000000),
            'percent_engaged_women_3yr' => $this->faker->numberBetween(0, 100),
            'percent_engaged_men_3yr' => $this->faker->numberBetween(0, 100),
            'percent_engaged_under_35_3yr' => $this->faker->numberBetween(0, 100),
            'percent_engaged_over_35_3yr' => $this->faker->numberBetween(0, 100),
            'percent_engaged_smallholder_3yr' => $this->faker->numberBetween(0, 100),
            'total_trees_grown' => $this->faker->numberBetween(0, 1000000),
            'avg_tree_survival_rate' => $this->faker->numberBetween(0, 100),
            'tree_maintenance_aftercare_approach' => $this->faker->text(500),
            'restored_areas_description' => $this->faker->text(500),
            'monitoring_evaluation_experience' => $this->faker->text(500),
            'funding_history' => [
                $this->faker->text(500),
            ],

            'engagement_farmers' => $this->faker->randomElements(
                ['we-provide-paid-jobs-for-farmers',
                'we-directly-engage-benefit-farmers',
                'we-provide-indirect-benefits-to-farmers',
                'we-do-not-engage-with-farmers', ],
                $this->faker->numberBetween(1, 3)
            ),
            'engagement_women' => $this->faker->randomElements(
                ['we-provide-paid-jobs-for-women',
                'we-directly-engage-benefit-women',
                'we-provide-indirect-benefits-to-women',
                'we-do-not-engage-with-women', ],
                $this->faker->numberBetween(1, 3)
            ),
            'engagement_youth' => $this->faker->randomElements(
                ['we-provide-paid-jobs-for-people-younger-than-35',
                'we-directly-engage-benefit-people-younger-than-35',
                'we-provide-indirect-benefits-to-people-younger-than-35',
                'we-do-not-engage-with-people-younger-than-35', ],
                $this->faker->numberBetween(1, 3)
            ),
            'engagement_non_youth' => $this->faker->randomElements(
                ['we-provide-paid-jobs-for-people-older-than-35',
                'we-directly-engage-benefit-people-older-than-35',
                'we-provide-indirect-benefits-to-people-older-than-35',
                'we-do-not-engage-with-people-older-than-35', ],
                $this->faker->numberBetween(1, 3)
            ),
            'restoration_types_implemented' => $this->faker->randomElements(
                [ 'agroforestry', 'applied-nucleation', 'assisted-natural-regeneration',
                'direct-seeding', 'enrichment-planting', 'mangrove-restoration',
                'reforestation', 'riparian-restoration', 'silvopasture',],
                $this->faker->numberBetween(1, 3)
            ),
            'tree_restoration_practices' => $this->faker->randomElements(
                [ 'tree-planting', 'assisted-natural-regeneration', 'direct-seeding', 'other'],
                $this->faker->numberBetween(1, 3)
            ),
        ];
    }
}
