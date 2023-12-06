<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

class ProgrammeImageExportControllerTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /*
     * Uncomment this if you need to work on testing the zip file contents
     * otherwise the supplied factory unit tests coverage is adequate
     *
    private static $configurationApp = null;

    public function setUp(): void
    {
        parent::setUp();
        if (is_null(self::$configurationApp)) {
            $app = require __DIR__ . '/../../bootstrap/app.php';
            $app->loadEnvironmentFrom('.env');
            $app->make(Kernel::class)->bootstrap();
            $this->seed();

            self::$configurationApp = $app;
            $this->app = $app;
        }
    }

    public function test_successful_invoke_action()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($user)
            ->getJson('/api/ppc/export/programme/1/images')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/ppc/export/programme/1/images')
            ->assertSuccessful();
    }
//    */

    public function test_invoke_action()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $programme = Programme::factory()->create();
        $uri = '/api/ppc/export/programme/' . $programme->id . '/images';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertStatus(422);
    }

    public function test_user_can_export_own()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $programme = Programme::factory()->create(['organisation_id' => $organisation->id]);
        $owner->programmes()->attach($programme->id);

        $uri = '/api/ppc/export/programme/' . $programme->id . '/images';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(422);
    }

    public function test_requires_programme_to_have_files()
    {
        $user = User::factory()->admin()->create();
        $programme = Programme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/ppc/export/programme/' . $programme->id .'/images')
            ->assertStatus(422);
    }
}
