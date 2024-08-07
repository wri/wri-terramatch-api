<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNoneTreeSpecies;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class TerrafundNoneTreeSpeciesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    public function testAddNoneTreeSpeciesToSiteSubmission(): void
    {
        $treeName = $this->faker->name();
        $treeAmount = $this->faker->numberBetween(1);
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $this->actingAs($user)
            ->postJson('/api/terrafund/none_tree_species', [
                'speciesable_type' => 'site_submission',
                'speciesable_id' => $siteSubmission->id,
                'name' => $treeName,
                'amount' => $treeAmount,
            ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'speciesable_type' => TerrafundSiteSubmission::class,
                'speciesable_id' => $siteSubmission->id,
                'name' => $treeName,
                'amount' => $treeAmount,

            ]);
    }

    public function test_add_bulk_none_tree_species_to_site_submission()
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/none_tree_species_bulk', [
                'speciesable_type' => 'site_submission',
                'speciesable_id' => $siteSubmission->id,
                'collection' => [
                    [
                        'name' => $this->faker->name,
                        'amount' => $this->faker->numberBetween(1, 100),
                    ],
                    [
                        'name' => $this->faker->name,
                        'amount' => $this->faker->numberBetween(1, 100),
                    ],
                ],
            ])
            ->assertStatus(201);

        $this->assertDatabaseCount('terrafund_none_tree_species', 2);
    }

    public function testAddNoneTreeSpeciesToSiteSubmissionUserNotInProgramme()
    {
        $treeName = $this->faker->name();
        $treeAmount = $this->faker->numberBetween(1);
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundProgrammeNotUser = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgrammeNotUser->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $this->actingAs($user)
            ->postJson('/api/terrafund/none_tree_species', [
                'speciesable_type' => 'site_submission',
                'speciesable_id' => $siteSubmission->id,
                'name' => $treeName,
                'amount' => $treeAmount,
            ])
            ->assertStatus(403);
    }

    public function testDeleteAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $species = TerrafundNoneTreeSpecies::factory()->create([
           'speciesable_id' => $siteSubmission->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/terrafund/none_tree_species/'.$species->id)
            ->assertStatus(200);
    }

    public function testDeleteActionNotInProgramme(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundProgrammeNotUser = TerrafundProgramme::factory()->create();

        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgrammeNotUser->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $species = TerrafundNoneTreeSpecies::factory()->create([
           'speciesable_id' => $siteSubmission->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/terrafund/none_tree_species/'.$species->id)
            ->assertStatus(403);
    }
}
