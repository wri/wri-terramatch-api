<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class TerrafundTreeSpeciesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    public function testAddTreeSpeciesToSiteSubmission(): void
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
            ->postJson('/api/terrafund/tree_species', [
                'treeable_type' => 'site_submission',
                'treeable_id' => $siteSubmission->id,
                'name' => $treeName,
                'amount' => $treeAmount,
            ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'treeable_type' => TerrafundSiteSubmission::class,
                'treeable_id' => $siteSubmission->id,
                'name' => $treeName,
                'amount' => $treeAmount,

            ]);
    }

    public function testAddTreeSpeciesToSiteSubmissionUserNotInProgramme(): void
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
            ->postJson('/api/terrafund/tree_species', [
                'treeable_type' => 'site_submission',
                'treeable_id' => $siteSubmission->id,
                'name' => $treeName,
                'amount' => $treeAmount,
            ])
            ->assertStatus(403);
    }

    public function testReadAllSiteTreesAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $trees = TerrafundTreeSpecies::factory()
            ->count(10)
            ->create([
            'treeable_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/site/tree_species')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.id', $trees[0]->id)
        ;
    }

    public function testReadAllNurseryTreesAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $trees = TerrafundTreeSpecies::factory()
            ->terrafundNursery()
            ->count(10)
            ->create([
            'treeable_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/nursery/tree_species')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.id', $trees[0]->id)
        ;
    }

    public function testBulkTreeSpeciesToSiteSubmission(): void
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

        for ($i = 0; $i < 3 ; $i++) {
            $collection[] = [
                'name' => $this->faker->name(),
                'amount' => $this->faker->numberBetween(1, 200),
            ];
        }

        $data = [
            'treeable_type' => 'site_submission',
            'treeable_id' => $siteSubmission->id,
            'collection' => $collection,
        ];

        $this->actingAs($user)
            ->postJson('/api/terrafund/tree_species_bulk', $data)
            ->assertStatus(201);
    }

    public function testBulkTreeSpeciesDuplicationToSiteSubmission(): void
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

        for ($i = 0; $i < 3 ; $i++) {
            $collection[] = [
                'name' => $this->faker->name(),
                'amount' => $this->faker->numberBetween(1, 200),
            ];
        }

        $collection[] = [
            'name' => $collection[0]['name'],
            'amount' => 20,
        ];

        $dupCheckTotal = $collection[0]['amount'] + 20;

        $data = [
            'treeable_type' => 'site_submission',
            'treeable_id' => $siteSubmission->id,
            'collection' => $collection,
        ];

        $this->actingAs($user)
            ->postJson('/api/terrafund/tree_species_bulk', $data)
            ->assertStatus(201);

        $dupeEntry = TerrafundTreeSpecies::where('treeable_type', TerrafundSiteSubmission::class)
           ->where('treeable_id', $siteSubmission->id)
           ->where('name', $collection[0]['name'])
           ->first();

        $this->assertEquals($dupCheckTotal, $dupeEntry->amount);
    }

    public function testBulkTreeSpeciesClearsOldOnSiteSubmission(): void
    {
        $user = User::factory()->create();
        $collection1 = [];
        $collection2 = [];
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        for ($i = 0; $i < 4 ; $i++) {
            $collection1[] = [
                'name' => $this->faker->name(),
                'amount' => $this->faker->numberBetween(1, 200),
            ];
        }

        $data = [
            'treeable_type' => 'site_submission',
            'treeable_id' => $siteSubmission->id,
            'collection' => $collection1,
        ];

        $this->actingAs($user)
            ->postJson('/api/terrafund/tree_species_bulk', $data)
            ->assertStatus(201);

        for ($i = 0; $i < 2 ; $i++) {
            $collection2[] = [
                'name' => $this->faker->name(),
                'amount' => $this->faker->numberBetween(1, 200),
            ];
        }

        $data = [
            'treeable_type' => 'site_submission',
            'treeable_id' => $siteSubmission->id,
            'collection' => $collection2,
        ];

        $this->actingAs($user)
            ->postJson('/api/terrafund/tree_species_bulk', $data)
            ->assertStatus(201);

        $count = TerrafundTreeSpecies::where('treeable_type', TerrafundSiteSubmission::class)
            ->where('treeable_id', $siteSubmission->id)
            ->count();

        $this->assertEquals($count, 2);
    }
}
