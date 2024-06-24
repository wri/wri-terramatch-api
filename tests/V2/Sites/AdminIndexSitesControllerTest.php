<?php

namespace Tests\V2\Sites;

use App\Models\Framework;
use App\Models\User;
use App\Models\V2\Sites\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminIndexSitesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('v2migration:roles');
        $this->user = User::factory()->admin()->create();
        Framework::factory()->create(['slug' => 'terrafund']);
        Framework::factory()->create(['slug' => 'ppc']);
        $this->user->givePermissionTo('framework-terrafund');

        Site::query()->delete();
        Site::factory()->count(5)->create(['framework_key' => 'terrafund']);
        Site::factory()->count(5)->create(['framework_key' => 'ppc']);
    }

    public function test_admins_can_view_site_index()
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/sites')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_site_index_has_required_filters()
    {
        $filters = [
            'country',
            'organisation_uuid',
            'project_uuid',
            'framework_key',
            'monitoring_data',
        ];

        foreach ($filters as $filter) {
            $this->actingAs($this->user)
                ->getJson('/api/v2/admin/sites?filter['.$filter.']=1')
                ->assertStatus(200);
        }
    }

    public function test_site_index_has_required_sorts()
    {
        $sortOrders = [
            'name',
            '-name',
            'status',
            '-status',
            'project_name',
            '-project_name',
            'establishment_date',
            '-establishment_date',
        ];

        foreach ($sortOrders as $sortOrder) {
            $this->actingAs($this->user)
                ->getJson('/api/v2/admin/sites?sort='.$sortOrder)
                ->assertStatus(200);
        }
    }

    public function test_site_index_has_required_fields()
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/sites')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'uuid',
                        'name',
                        'status',
                        'framework_key',
                    ],
                ],
            ]);
    }

    public function test_non_admins_cannot_view_site_index()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/sites')
            ->assertStatus(403);
    }
}
