<?php

namespace Database\Factories\V2;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectPitchFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        $restorationInterventionTypes = [ 'agroforestry', 'applied-nucleation', 'assisted-natural-regeneration',
            'direct-seeding', 'enrichment-planting', 'mangrove-restoration',
            'reforestation', 'riparian-restoration', 'silvopasture',
        ];

        $capacityBuildingNeeds = ['site-selection', 'nursery-management', 'species',
            'community-engagement', 'narrative', 'field-monitoring', 'remote-sensing', 'accounting',
            'proposal', 'government', 'certifications', 'communications',
            'social-equity', 'supply-chain-development', 'product-marketing',
        ];

        $sustainableDevelopmentGoals = ['no-poverty', 'zero-hunger', 'good-health-and-well-being',
            'quality-education', 'gender-equality', 'clean-water-and-sanitation', 'affordable-and-clean-energy',
            'decent-work-and-economic-growth', 'industry-innovation-and-infrastructure', 'reduced-inequalities',
            'sustainable-cities-and-communities', 'responsible-consumption-and-production', 'climate-action',
            'life-below-water', 'life-on-land', 'peace-justice-and-strong-institutions', 'partnerships-for-the-goals',
        ];

        $landTenures = ['public', 'private', 'indigenous', 'communal', 'national_protected_area', 'other'];

        return [
            'project_name' => $this->faker->word(),
            'project_objectives' => $this->faker->paragraph(),
            'project_country' => $this->faker->randomElement(array_keys(config('wri.countries'))),
            'project_county_district' => $this->faker->word(),
            'restoration_intervention_types' => $this->faker->randomElements($restorationInterventionTypes, $this->faker->numberBetween(1, 5)),
            'total_hectares' => $this->faker->numberBetween(0, 100000),
            'total_trees' => $this->faker->numberBetween(0, 100000),
            'project_budget' => $this->faker->numberBetween(0, 100000),
            'capacity_building_needs' => $this->faker->randomElements($capacityBuildingNeeds, $this->faker->numberBetween(1, 3)),
            'organisation_id' => Organisation::factory()->create()->uuid,
            'funding_programme_id' => FundingProgramme::factory()->create()->uuid,
            'expected_active_restoration_start_date' => $this->faker->date,
            'expected_active_restoration_end_date' => $this->faker->date,
            'description_of_project_timeline' => $this->faker->text,
            'proj_partner_info' => $this->faker->text,
            'land_tenure_proj_area' => $this->faker->randomElements($landTenures, $this->faker->numberBetween(1, 5)),
            'landholder_comm_engage' => $this->faker->text,
            'proj_success_risks' => $this->faker->text,
            'monitor_eval_plan' => $this->faker->text,
            'proj_boundary' => $this->faker->text,
            'sustainable_dev_goals' => $this->faker->randomElements($sustainableDevelopmentGoals, $this->faker->numberBetween(1, 5)),
            'proj_area_description' => $this->faker->text,
            'proposed_num_sites' => $this->faker->numberBetween(0, 9999999),
            'environmental_goals' => $this->faker->text,
            'proposed_num_nurseries' => $this->faker->numberBetween(0, 9999999),
            'curr_land_degradation' => $this->faker->text,
            'proj_impact_socieconom' => $this->faker->text,
            'proj_impact_foodsec' => $this->faker->text,
            'proj_impact_watersec' => $this->faker->text,
            'proj_impact_jobtypes' => $this->faker->text,
            'num_jobs_created' => $this->faker->numberBetween(0, 9999999),
            'pct_employees_men' => $this->faker->numberBetween(0, 100),
            'pct_employees_women' => $this->faker->numberBetween(0, 100),
            'pct_employees_18to35' => $this->faker->numberBetween(0, 100),
            'pct_employees_older35' => $this->faker->numberBetween(0, 100),
            'proj_beneficiaries' => $this->faker->numberBetween(0, 9999999),
            'pct_beneficiaries_women' => $this->faker->numberBetween(0, 100),
            'pct_beneficiaries_small' => $this->faker->numberBetween(0, 100),
            'pct_beneficiaries_large' => $this->faker->numberBetween(0, 100),
            'pct_beneficiaries_youth' => $this->faker->numberBetween(0, 100),
        ];
    }
}
