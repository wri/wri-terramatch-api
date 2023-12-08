<?php

namespace Tests\V2\NurseryReports;

use App\Models\User;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminIndexNurseryReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        // Artisan::call('v2migration:roles');
        $tfAdmin = User::factory()->admin()->create();
        $ppcAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');
        $ppcAdmin->givePermissionTo('framework-ppc');
        $user = User::factory()->create();

        NurseryReport::query()->delete();
        NurseryReport::factory()->count(3)->create(['framework_key' => 'terrafund']);
        NurseryReport::factory()->count(5)->create(['framework_key' => 'ppc']);

        // This will create a soft deleted project that should not appear on the results
        (NurseryReport::factory()->create(['framework_key' => 'ppc']))->delete();
        (NurseryReport::factory()->create(['framework_key' => 'terrafund']))->delete();

        $uri = '/api/v2/admin/nursery-reports';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }
}
