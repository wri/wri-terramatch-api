<?php

namespace Database\Factories\V2\Projects;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $projStatus = ['new_project', 'existing_expansion'];
        $frameworks = ['ppc', 'terrafund'];
        $country = ['ZW', 'NA', 'RW', 'UG'];
        $geojson = '{"type":"FeatureCollection","features":[{"type":"Feature","properties":{"Location_N":"OhBoySoy","Company_ID":17,"Cadaster_I":"CAR_12361","Volume":300,"Owner_Name":"Uche","Owner_ID":"CPF","Facility_N":"Silo","Facility_E":"hello@ohboysoy.com","Company_Na":"PlusSoy","Company_Co":"Uche","Certificat":"AAPRESID; ISCC","Start_Date":"2012-03-30","End_Date":"2013-03-14","Time_Horiz":"1 year","TestA":"agu","TestB":"busu","TestC":"cheretu"},"geometry":{"type":"Polygon","coordinates":[[[-50.30542917000004,-24.634982327999975],[-50.305427671,-24.63525325399992],[-50.303945649999946,-24.635246400999975],[-50.303944149,-24.63551732699992],[-50.30305493299997,-24.635513207999935],[-50.302758527999956,-24.63551183399991],[-50.30275552099997,-24.63605368599998],[-50.30245911499996,-24.636052310999954],[-50.30245610599998,-24.636594161999934],[-50.30097406900001,-24.636587279999898],[-50.30097256199998,-24.636858205999932],[-50.300676153999966,-24.636856826999907],[-50.30067464500002,-24.63712775399994],[-50.30037823700001,-24.637126373999916],[-50.299785419999985,-24.637123614999954],[-50.29978844000006,-24.636581763999974],[-50.299195626000035,-24.636579001999923],[-50.299194114,-24.636849927999958],[-50.29860129899998,-24.636847162999906],[-50.29859978599994,-24.63711808899994],[-50.298006969,-24.63711532199989],[-50.29800545499997,-24.637386247999924],[-50.298598273,-24.637389014999886],[-50.29859675999996,-24.63765993999992],[-50.29800394099993,-24.63765717399996],[-50.298002426999986,-24.637928098999904],[-50.297706016999975,-24.637926714999878],[-50.29770147200004,-24.63873949099998],[-50.29770003900001,-24.63899588499988],[-50.297996941999976,-24.638909625999936],[-50.29829384399994,-24.638823366999905],[-50.298568614000025,-24.63874353899996],[-50.298887647999955,-24.63865084999993],[-50.30363801699998,-24.63727072699987],[-50.30363873299996,-24.637141503999924],[-50.30407582699997,-24.637143529999914],[-50.306310055,-24.63649442099994],[-50.306310901999986,-24.636341062999954],[-50.30571808799996,-24.636338326999905],[-50.305719584,-24.63606739999996],[-50.30601599100001,-24.636068768999895],[-50.306018984,-24.635526916999915],[-50.30661179399992,-24.635529650999963],[-50.306613290000044,-24.63525872599993],[-50.306316884999944,-24.635257359],[-50.30631838099998,-24.63498643299987],[-50.30542917000004,-24.634982327999975]]]}}]}';
        $landUse = ['agroforest', 'mangrove', 'natural-forest', 'silvopasture', 'riparian-area-or-wetland', 'urban-forest', 'woodlot-or-plantation',];
        $restorationStrat = ['assisted-natural-regeneration', 'direct-seeding', 'tree-planting',];
        $sdgsImpactedType = ['no-poverty', 'zero-hunger', 'good-health-and-well-being', 'quality-education', 'gender-equality', 'clean-water-and-sanitation',
            'affordable-and-clean-energy', 'decent-work-and-economic-growth', 'industry-innovation-and-infrastructure', 'reduced-inequalities',
            'sustainable-cities-and-communities', 'responsible-consumption-and-production', 'climate-action', 'life-below-water', 'life-on-land',
            'peace-justice-and-strong-institutions', 'partnerships-for-the-goals',
        ];


        return [
            'framework_key' => $this->faker->randomElement($frameworks),
            'name' => $this->faker->words(3, true),
            'status' => array_keys(Project::$statuses)[0],
            'project_status' => $this->faker->randomElement($projStatus),
            'organisation_id' => Organisation::factory()->create()->id,
            'boundary_geojson' => $geojson,
            'country' => $this->faker->randomElement($country),
            'continent' => 'Africa',
            'planting_start_date' => $this->faker->date(),
            'planting_end_date' => $this->faker->date(),
            'description' => $this->faker->text(500),
            'budget' => $this->faker->numberBetween(0, 9999999),
            'history' => $this->faker->text(500),
            'objectives' => $this->faker->text(500),
            'environmental_goals' => $this->faker->text(500),
            'socioeconomic_goals' => $this->faker->text(500),
            'long_term_growth' => $this->faker->text(500),
            'community_incentives' => $this->faker->text(500),
            'jobs_created_goal' => $this->faker->numberBetween(0, 9999999),
            'total_hectares_restored_goal' => $this->faker->numberBetween(0, 9999999),
            'trees_grown_goal' => $this->faker->numberBetween(0, 9999999),
            'survival_rate' => $this->faker->numberBetween(0, 99),
            'year_five_crown_cover' => $this->faker->numberBetween(0, 99),
            'land_use_types' => $this->faker->randomElements($landUse, $this->faker->numberBetween(0, 7), false),
            'restoration_strategy' => $this->faker->randomElements($restorationStrat, $this->faker->numberBetween(0, 3), false),
            'sdgs_impacted' => $this->faker->randomElements($sdgsImpactedType, $this->faker->numberBetween(0, 8), false),
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
}
