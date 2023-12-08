<?php

namespace Tests\Feature;

use App\Models\Aim;
use App\Models\EditHistory;
use App\Models\OrganisationVersion;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class EditHistoryControllerTest extends TestCase
{
    use WithFaker;

    private $modelTypes = [
        'site',
        'programme',
        'terrafundProgramme',
        'terrafundSite',
        'terrafundNursery',
    ];

    public function testReadEditHistoryAction(): void
    {
        $owner = User::factory()->create();
        $alien = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $record = EditHistory::factory()->create([
            'status' => EditHistory::STATUS_REQUESTED,
            'created_by_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->getJson('/api/edit-history/' . $record->uuid)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $record->uuid,
                'status' => EditHistory::STATUS_REQUESTED,
            ]);

        $this->actingAs($admin)
            ->getJson('/api/edit-history/' . $record->uuid)
            ->assertSuccessful();

        $this->actingAs($alien)
            ->getJson('/api/edit-history/' . $record->uuid)
            ->assertStatus(403);
    }

    public function testReadLatestEditHistoryAction(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $record = EditHistory::factory()->create([
            'status' => EditHistory::STATUS_REQUESTED,
            'created_by_user_id' => $owner->id,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/edit-history/' . $record->model_type . '/' . $record->editable_id)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $record->uuid,
                'status' => EditHistory::STATUS_REQUESTED,
            ]);

        $project = Programme::factory()->create();

        $this->actingAs($admin)
            ->getJson('/api/edit-history/programme/' .$project->id)
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function testUpdateEditHistoryAction(): void
    {
        $owner = User::factory()->create();
        $alien = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $record = EditHistory::factory()->site()->create([
            'status' => EditHistory::STATUS_REQUESTED,
            'created_by_user_id' => $owner->id,
        ]);

        $site = Site::factory()->create();

        $payload = [
            'content' => json_encode($site),
        ];

        $this->actingAs($owner)
            ->putJson('/api/edit-history/' . $record->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'content' => json_encode($site),
            ]);

        $this->actingAs($admin)
            ->putJson('/api/edit-history/' . $record->uuid, $payload)
            ->assertSuccessful();

        $this->actingAs($alien)
            ->putJson('/api/edit-history/' . $record->uuid, $payload)
            ->assertStatus(403);
    }

    public function testAdminApproveEditHistoryAction(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();

        foreach ($this->modelTypes as $modelType) {
            $record = EditHistory::factory()
                ->$modelType()
                ->create([
                    'status' => EditHistory::STATUS_REQUESTED,
                    'created_by_user_id' => $owner->id,
                    'content' => json_encode(['name' => 'test']),
                ]);

            $payload = [
                'uuid' => $record->uuid,
            ];

            $this->actingAs($admin)
                ->putJson('/api/edit-history/approve',  $payload)
                ->assertSuccessful()
                ->assertJsonFragment(['status' => 'approved']);
        }

        $this->actingAs($owner)
            ->getJson('/api/notifications')
            ->assertSuccessful()
            ->assertJsonCount(count($this->modelTypes), 'data');
    }

    public function testUserCannotApproveEditHistoryAction(): void
    {
        $owner = User::factory()->create();

        foreach ($this->modelTypes as $modelType) {
            $record = EditHistory::factory()
                ->$modelType()
                ->create([
                    'status' => EditHistory::STATUS_REQUESTED,
                    'created_by_user_id' => $owner->id,
                ]);

            $payload = [
                'uuid' => $record->uuid,
            ];

            $this->actingAs($owner)
                ->putJson('/api/edit-history/approve',  $payload)
                ->assertStatus(403);
        }
    }

    public function testRejectEditHistoryAction(): void
    {
        $admin = User::factory()->admin()->create();
        $record = EditHistory::factory()->create(['status' => EditHistory::STATUS_REQUESTED]);
        $owner = $record->createdBy;

        $payload = [
            'uuid' => $record->uuid,
            'comments' => $this->faker->text(80),
        ];

        $this->actingAs($owner)
            ->putJson('/api/edit-history/reject',  $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/edit-history/reject',  $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'rejected',
                'comments' => $payload['comments'],
            ]);

        $this->actingAs($owner)
            ->getJson('/api/notifications')
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => 'Rejected Update Request',
                'referenced_model' => $record->editable_type,
                'referenced_model_id' => $record->editable_id,
            ]);
    }

    public function testListEditHistoryAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        EditHistory::factory()->count(7)->create();

        $this->actingAs($user)
            ->getJson('/api/edit-history')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/edit-history')
            ->assertStatus(200)
            ->assertJsonCount(7, 'data');
    }

    public function testPaginatedListEditHistoryAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        EditHistory::factory()->count(17)->create();

        $this->actingAs($user)
            ->getJson('/api/edit-history')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/edit-history')
            ->assertStatus(200)
            ->assertJsonCount(15, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=5')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=5&page=4')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testListEditHistoryForStatusAction(): void
    {
        $admin = User::factory()->admin()->create();

        $i = 2;
        foreach (EditHistory::$statuses as $key => $value) {
            EditHistory::factory()->count($i)->create(['status' => $key]);
            $i++;
        }

        $i = 2;
        foreach (EditHistory::$statuses as $key => $value) {
            $this->actingAs($admin)
                ->getJson('/api/edit-history?status=' . $key)
                ->assertStatus(200)
                ->assertJsonCount($i, 'data');
            $i++;
        }

        $i = 2;
        $count = 0;
        foreach (EditHistory::$statuses as $key => $value) {
            $count += $i;
            $keys = empty($keys) ? $key : $keys . ',' . $key;
            $this->actingAs($admin)
                ->getJson('/api/edit-history?status=' . $keys)
                ->assertStatus(200)
                ->assertJsonCount($count, 'data');
            $i++;
        }
    }

    public function testSearchEditHistoryAction(): void
    {
        $admin = User::factory()->admin()->create();
        EditHistory::factory()->programme()->count(15)->create();
        EditHistory::factory()->programme(['name' => 'babs95 testing 1'])->count(4)->create(['status' => EditHistory::STATUS_REQUESTED]);
        EditHistory::factory()->programme(['name' => 'Sandra78 testing 2'])->count(3)->create(['status' => EditHistory::STATUS_REQUESTED]);
        EditHistory::factory()->programme(['name' => 'Sandra78 testing 2'])->count(2)->create(['status' => EditHistory::STATUS_APPROVED]);
        EditHistory::factory()->terrafundProgramme(['name' => 'Babs95 testing 3'])->count(7)->create(['status' => EditHistory::STATUS_REQUESTED]);
        EditHistory::factory()->terrafundProgramme(['name' => 'Babs95 testing 3'])->count(9)->create(['status' => EditHistory::STATUS_APPROVED]);
        EditHistory::factory()->terrafundProgramme(['name' => 'Trevor67 testing 4'])->count(6)->create();

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&search=terrafund')
            ->assertStatus(200)
            ->assertJsonCount(22, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&search=ppc')
            ->assertStatus(200)
            ->assertJsonCount(24, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&search=trevor67')
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&search=babs95')
            ->assertStatus(200)
            ->assertJsonCount(20, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&search=sandra78')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&search=testing%204')
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=10&sort=organisation&order=asc')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=10&sort=framework&order=desc')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=10&sort=project&order=desc')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $this->actingAs($admin)
            ->getJson('/api/edit-history?items=50&status=requested&search=babs95')
            ->assertStatus(200)
            ->assertJsonCount(11, 'data');
    }

    public function testEditHistoryCreateForPPCProgrammeAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->programmes()->attach($project->id);

        $updatedProject = Programme::factory()->make(['organisation_id' => $orgVersion->organisation_id]);

        $payload = [
            'editable_type' => 'programme',
            'editable_id' => $project->id,
            'content' => json_encode($updatedProject),
        ];

        $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'requested',
                'organisation_name' => $orgVersion->name,
                'framework_name' => 'PPC',
                'project_name' => $project->name,
                'content' => json_encode($updatedProject),
                'model' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'type' => 'programme',
                ],
            ]);
    }

    public function testSpecificEditHistoryCreateForPPCProgrammeAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->programmes()->attach($project->id);
        Aim::factory()->create(['programme_id' => $project->id]);

        $updatedProject = [
            'boundary_geojson' => json_encode([
                'type' => 'FeatureCollection',
                'features' => [
                    'type' => 'Feature',
                    'properties' => ['PolygonID' => 1],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [[-56.457818,-32.306839],[-56.565395,-32.315748],[-56.6689,-32.342136],[-56.764408,-32.385004],[-56.848284,-32.442728],[-56.917318,-32.513114],[-56.968845,-32.593484],[-57.000852,-32.680772],[-57.012056,-32.77164],[-57.001963,-32.8626],[-56.970897,-32.950153],[-56.919999,-33.030919],[-56.851186,-33.101772],[-56.767089,-33.159963],[-56.670952,-33.203227],[-56.566506,-33.229879],[-56.457818,-33.238881],[-56.34913,-33.229879],[-56.244683,-33.203227],[-56.148546,-33.159963],[-56.064449,-33.101772],[-55.995636,-33.030919],[-55.944738,-32.950153],[-55.913672,-32.8626],[-55.903579,-32.77164],[-55.914783,-32.680772],[-55.94679,-32.593484],[-55.998317,-32.513114],[-56.067351,-32.442728],[-56.151227,-32.385004],[-56.246735,-32.342136],[-56.35024,-32.315748],[-56.457818,-32.306839],],
                        ],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'PolygonID' => 2,
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [-55.868225,-32.899191],
                    ],
                ],
            ]),
            'aims' => [
                'year_five_trees' => '81',
                'restoration_hectares' => '81',
                'survival_rate' => '81',
                'year_five_crown_cover' => '81',
            ],
        ];

        $payload = [
            'editable_type' => 'programme',
            'editable_id' => $project->id,
            'content' => json_encode($updatedProject),
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200);

        $uuid = $response->getOriginalContent()['uuid'];

        $this->actingAs($admin)
            ->putJson('/api/edit-history/approve',  ['uuid' => $uuid])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'approved']);
    }

    public function testSpecificEditHistoryCreateForPPCSiteAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->programmes()->attach($project->id);
        $site = Site::factory()->create(['programme_id' => $project->id, 'control_site' => false]);

        $geojson = json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                'type' => 'Feature',
                'properties' => [
                    'Name' => 'test',
                    'IntervType' => 'Agroforestry',
                    'PolygonID' => 1,
                    'SiteName' => 'Tue 20 Dec',
                    'Country' => 'Uruguay',
                    'PlantDate' => '20/12/2022',
                    'EstablishmentDate' => '20/12/2022',
                ],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[[-56.238434,-32.261276],[-56.352374,-32.270713],[-56.462005,-32.298668],[-56.563172,-32.344082],[-56.652028,-32.405235],[-56.725173,-32.479807],[-56.779784,-32.564961],[-56.813723,-32.657449],[-56.825627,-32.753736],[-56.814969,-32.850126],[-56.782086,-32.942912],[-56.728181,-33.02851],[-56.655284,-33.103605],[-56.56618,-33.165282],[-56.464307,-33.211141],[-56.35362,-33.239392],[-56.238434,-33.248934],[-56.123248,-33.239392],[-56.012562,-33.211141],[-55.910689,-33.165282],[-55.821585,-33.103605],[-55.748687,-33.02851],[-55.694783,-32.942912],[-55.6619,-32.850126],[-55.651242,-32.753736],[-55.663146,-32.657449],[-55.697085,-32.564961],[-55.751696,-32.479807],[-55.824841,-32.405235],[-55.913697,-32.344082],[-56.014864,-32.298668],[-56.124494,-32.270713],[-56.238434,-32.261276]]],
                ],
            ],
            [
                'type' => 'Feature',
                'properties' => [
                    'Name' => 'test',
                    'IntervType' => 'Control',
                    'PolygonID' => 2,
                    'SiteName' => 'Tue 20 Dec',
                    'Country' => 'Uruguay',
                    'EstablishmentDate' => '20/12/2022',
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [-55.604553,-32.793335],
                ],
            ],
        ]);

        $updatedSite = [
            'name' => 'Tue 20 Dec',
            'description' => 'Control site for edit testing edit',
            'history' => 'No edit',
            'boundary_geojson' => $geojson,
            'site_restoration_methods' => [],
            'site_land_tenures' => [],
            'aim_survival_rate' => 12,
            'aim_year_five_crown_cover' => 43,
            'aim_direct_seeding_survival_rate' => 32,
            'aim_natural_regeneration_trees_per_hectare' => 23,
            'aim_natural_regeneration_hectares' => 23,
            'aim_number_of_mature_trees' => '30',
            'aim_soil_condition' => 'severely_degraded',
            'planting_pattern' => null,
            'seeds' => [],
            'stratification_for_heterogeneity' => null,
            'invasives' => [
                [
                    'type' => 'common',
                    'name' => 'test',
                ],
                [
                    'type' => 'uncommon',
                    'name' => 'Update',
                ],
            ],
        ];

        $payload = [
            'editable_type' => 'site',
            'editable_id' => $site->id,
            'content' => json_encode($updatedSite),
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200);

        $uuid = $response->getOriginalContent()['uuid'];
        $this->actingAs($admin)
            ->putJson('/api/edit-history/approve',  ['uuid' => $uuid])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'approved']);
    }

    public function testSpecificEditHistoryCreateForPPCControlSiteAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->programmes()->attach($project->id);
        $site = Site::factory()->create(['programme_id' => $project->id, 'control_site' => true]);

        $geojson = json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                'type' => 'Feature',
                'properties' => [
                    'Name' => 'test',
                    'IntervType' => 'Agroforestry',
                    'PolygonID' => 1,
                    'SiteName' => 'Tue 20 Dec',
                    'Country' => 'Uruguay',
                    'PlantDate' => '20/12/2022',
                    'EstablishmentDate' => '20/12/2022',
                ],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[[-56.238434,-32.261276],[-56.352374,-32.270713],[-56.462005,-32.298668],[-56.563172,-32.344082],[-56.652028,-32.405235],[-56.725173,-32.479807],[-56.779784,-32.564961],[-56.813723,-32.657449],[-56.825627,-32.753736],[-56.814969,-32.850126],[-56.782086,-32.942912],[-56.728181,-33.02851],[-56.655284,-33.103605],[-56.56618,-33.165282],[-56.464307,-33.211141],[-56.35362,-33.239392],[-56.238434,-33.248934],[-56.123248,-33.239392],[-56.012562,-33.211141],[-55.910689,-33.165282],[-55.821585,-33.103605],[-55.748687,-33.02851],[-55.694783,-32.942912],[-55.6619,-32.850126],[-55.651242,-32.753736],[-55.663146,-32.657449],[-55.697085,-32.564961],[-55.751696,-32.479807],[-55.824841,-32.405235],[-55.913697,-32.344082],[-56.014864,-32.298668],[-56.124494,-32.270713],[-56.238434,-32.261276]]],
                ],
            ],
            [
                'type' => 'Feature',
                'properties' => [
                    'Name' => 'test',
                    'IntervType' => 'Control',
                    'PolygonID' => 2,
                    'SiteName' => 'Tue 20 Dec',
                    'Country' => 'Uruguay',
                    'EstablishmentDate' => '20/12/2022',
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [-55.604553,-32.793335],
                ],
            ],
        ]);

        $updatedSite = [
            'name' => 'control site 3693',
            'description' => 'test edit',
            'history' => 'test edit',
            'boundary_geojson' => $geojson,
            'site_restoration_methods' => [],
            'site_land_tenures' => ['public'],
            'aim_survival_rate' => null,
            'aim_year_five_crown_cover' => null,
            'aim_direct_seeding_survival_rate' => null,
            'aim_natural_regeneration_trees_per_hectare' => null,
            'aim_natural_regeneration_hectares' => null,
            'aim_number_of_mature_trees' => '30',
            'aim_soil_condition' => 'severely_degraded',
            'planting_pattern' => null,
            'seeds' => [],
            'stratification_for_heterogeneity' => null,
            'invasives' => [
                [
                    'type' => 'dominant_species',
                    'name' => 'invasive',
                ],
                [
                    'type' => 'common',
                    'name' => 'new',
                ],
            ],
        ];

        $payload = [
            'editable_type' => 'site',
            'editable_id' => $site->id,
            'content' => json_encode($updatedSite),
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200);

        $uuid = $response->getOriginalContent()['uuid'];
        $this->actingAs($admin)
            ->putJson('/api/edit-history/approve',  ['uuid' => $uuid])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'approved']);
    }

    public function testEditHistoryCreateForPPCSiteAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->programmes()->attach($project->id);

        $site = Site::factory()->create(['programme_id' => $project->id]);
        $updatedSite = Site::factory()->make(['programme_id' => $project->id]);

        $payload = [
            'editable_type' => 'site',
            'editable_id' => $site->id,
            'content' => json_encode($updatedSite),
        ];

        $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'requested',
                'organisation_name' => $orgVersion->name,
                'framework_name' => 'PPC',
                'project_name' => $project->name,
                'content' => json_encode($updatedSite),
                'model' => [
                    'name_with_id' => $site->name_with_id,
                    'control_site' => false,
                    'id' => $site->id,
                    'name' => $site->name,
                    'type' => 'site',
                ],
            ]);
    }

    public function testEditHistoryCreateForTerrafundProgrammeAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->terrafundProgrammes()->attach($project->id);

        $updatedProject = TerrafundProgramme::factory()->make(['organisation_id' => $orgVersion->organisation_id]);

        $payload = [
            'editable_type' => 'terrafund_programme',
            'editable_id' => $project->id,
            'content' => json_encode($updatedProject),
        ];

        $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'requested',
                'organisation_name' => $orgVersion->name,
                'framework_name' => 'Terrafund',
                'project_name' => $project->name,
                'content' => json_encode($updatedProject),
                'model' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'type' => 'terrafund_programme',
                ],
            ]);
    }

    public function testEditHistoryTreeSpeciesForTerrafundProgrammeAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->terrafundProgrammes()->attach($project->id);

        TerrafundTreeSpecies::factory()
            ->count(3)
            ->create([
                'treeable_type' => TerrafundProgramme::class,
                'treeable_id' => $project->id,
            ]);

        $updatedProject = TerrafundProgramme::factory()->make(['organisation_id' => $orgVersion->organisation_id]);
        $updatedProject->tree_species = [
            ['name' => 'Silver Birch', 'amount' => 25 ],
            ['name' => 'Pine', 'amount' => 75],
        ];

        $payload = [
            'editable_type' => 'terrafund_programme',
            'editable_id' => $project->id,
            'content' => json_encode($updatedProject),
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertSuccessful();

        $this->assertCount(3, $project->fresh()->terrafundTreeSpecies);

        $uuid = $response->getOriginalContent()['uuid'];
        $this->actingAs($admin)
            ->putJson('/api/edit-history/approve',  ['uuid' => $uuid])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'approved']);

        $this->assertCount(2, $project->fresh()->terrafundTreeSpecies);
    }

    public function testEditHistoryTreeSpeciesForTerrafundNurseryAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->terrafundProgrammes()->attach($project->id);

        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $project->id,
        ]);

        TerrafundTreeSpecies::factory()
            ->count(3)
            ->create([
                'treeable_type' => TerrafundNursery::class,
                'treeable_id' => $nursery->id,
            ]);

        $updatedNursery = TerrafundNursery::factory()->make(['terrafund_programme_id' => $project->id]);
        $updatedNursery->tree_species = [
            ['name' => 'Silver Birch', 'amount' => 25 ],
            ['name' => 'Pine', 'amount' => 75],
        ];

        $payload = [
            'editable_type' => 'terrafund_nursery',
            'editable_id' => $nursery->id,
            'content' => json_encode($updatedNursery),
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertSuccessful();

        $this->assertCount(3, $nursery->fresh()->terrafundTreeSpecies);

        $uuid = $response->getOriginalContent()['uuid'];
        $this->actingAs($admin)
            ->putJson('/api/edit-history/approve',  ['uuid' => $uuid])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'approved']);

        $this->assertCount(2, $nursery->fresh()->terrafundTreeSpecies);
    }

    public function testEditHistoryCreateForTerrafundSiteAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->terrafundProgrammes()->attach($project->id);

        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $project->id,
        ]);

        $updatedSite = TerrafundSite::factory()->make([
            'terrafund_programme_id' => $project->id,
        ]);

        $payload = [
            'editable_type' => 'terrafund_site',
            'editable_id' => $site->id,
            'content' => json_encode($updatedSite),
        ];

        $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'requested',
                'organisation_name' => $orgVersion->name,
                'framework_name' => 'Terrafund',
                'project_name' => $project->name,
                'content' => json_encode($updatedSite),
                'model' => [
                    'id' => $site->id,
                    'name' => $site->name,
                    'type' => 'terrafund_site',
                ],
            ]);
    }

    public function testEditHistoryCreateForTerrafundNurseryAction(): void
    {
        $orgVersion = OrganisationVersion::factory()->create();

        $user = User::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $user->frameworks()->attach($project->framework_id);
        $user->terrafundProgrammes()->attach($project->id);

        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $project->id,
        ]);

        $updatedNursery = TerrafundNursery::factory()->make([
            'terrafund_programme_id' => $project->id,
        ]);

        $payload = [
            'editable_type' => 'terrafund_nursery',
            'editable_id' => $nursery->id,
            'content' => json_encode($updatedNursery),
        ];

        $this->actingAs($user)
            ->postJson('/api/edit-history', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'requested',
                'organisation_name' => $orgVersion->name,
                'framework_name' => 'Terrafund',
                'project_name' => $project->name,
                'content' => json_encode($updatedNursery),
                'model' => [
                    'id' => $nursery->id,
                    'name' => $nursery->name,
                    'type' => 'terrafund_nursery',
                ],
            ]);
    }
}
