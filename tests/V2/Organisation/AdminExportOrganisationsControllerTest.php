<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminExportOrganisationsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $organisations = Organisation::factory()->count(10)->create(['status' => Organisation::STATUS_APPROVED]);
        Organisation::factory()->count(11)->create(['status' => Organisation::STATUS_DRAFT]);
        Organisation::factory()->count(12)->create(['status' => Organisation::STATUS_REJECTED]);

        for ($i = 2; $i < 6 ; $i++) {
            TreeSpecies::factory()->count(3)->create([
                'speciesable_type' => Organisation::class,
                'speciesable_id' => $organisations[$i],
            ]);
        }

        $this->actingAs($user)
            ->getJson('/api/v2/admin/organisations/export')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations/export')
            ->assertSuccessful();
    }
}
