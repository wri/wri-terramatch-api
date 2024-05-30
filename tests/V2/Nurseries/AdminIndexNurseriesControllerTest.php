<?php

namespace Tests\V2\Nurseries;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminIndexNurseriesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('v2migration:roles');
        $this->user = User::factory()->admin()->create();
        $this->user->givePermissionTo('framework-terrafund');
        $this->user->givePermissionTo('framework-ppc');

        Nursery::query()->delete();
        Nursery::factory()->count(5)->create();
    }

    public function test_admins_can_view_nursery_index()
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/nurseries')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_nursery_index_has_required_fields()
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/nurseries')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'name',
                        'project' => [
                            'name',
                            'organisation',
                        ],
                        'status',
                        'establishment_date',
                        'framework_key',
                    ],
                ],
            ]);
    }

    public function test_nursery_index_has_required_filters()
    {
        $filters = [
            'country',
            'organisation_uuid',
            'project_uuid',
            'framework_key',
        ];

        foreach ($filters as $filter) {
            $this->actingAs($this->user)
                ->getJson('/api/v2/admin/nurseries?filter['.$filter.']=1')
                ->assertStatus(200);
        }
    }

    public function test_nursery_index_has_required_sorts()
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
                ->getJson('/api/v2/admin/nurseries?sort='.$sortOrder)
                ->assertStatus(200);
        }
    }

    public function test_non_admins_cannot_view_nursery_index()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/nurseries')
            ->assertStatus(403);
    }
}
