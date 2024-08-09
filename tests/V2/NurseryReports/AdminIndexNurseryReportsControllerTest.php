<?php

namespace Tests\V2\NurseryReports;

use App\Models\Framework;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminIndexNurseryReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();
        Framework::factory()->create(['slug' => 'terrafund']);
        Framework::factory()->create(['slug' => 'ppc']);
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
