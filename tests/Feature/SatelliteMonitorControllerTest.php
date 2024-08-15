<?php

namespace Tests\Feature;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;

final class SatelliteMonitorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateActionForTerrafundProgramme(): void
    {
        $user = User::factory()->admin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $id = $this->uploadMap($user);
        $data = [
            'satellite_monitorable_type' => TerrafundProgramme::class,
            'satellite_monitorable_id' => $terrafundProgramme->id,
            'map' => $id,
            'alt_text' => 'Lorem ipsum dolor sit amet',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/satellite_monitor', $data);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'satellite_monitorable_type',
                'satellite_monitorable_id',
                'map',
                'alt_text',
                'created_at',
            ],
        ]);
    }

    public function testReadLatestByTerrafundProgramme(): void
    {
        $user = User::factory()->admin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $resp = [];
        for ($i = 0;$i < 3;$i++) {
            Carbon::setTestNow(Carbon::now()->addMinutes($i));
            $id = $this->uploadMap($user);
            $data = [
                'satellite_monitorable_type' => TerrafundProgramme::class,
                'satellite_monitorable_id' => $terrafundProgramme->id,
                'map' => $id,
                'alt_text' => 'Lorem ipsum dolor sit amet',
            ];
            $this->actingAs($user)
                  ->postJson('/api/satellite_monitor', $data);
        }
        Carbon::setTestNow();


        $this->actingAs($user)
            ->getJson('/api/satellite_monitor/terrafund_programme/'.$terrafundProgramme->id.'/latest')
            ->assertOk()
            ->assertJsonPath('data.id', 4)
            ->assertJsonStructure(['data' => [
                'satellite_monitorable_type',
                'satellite_monitorable_id',
                'map',
                'alt_text',
            ],
            ]);
    }

    public function testReadAllByTerrafundProgramme(): void
    {
        $user = User::factory()->admin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $map = $this->uploadMap($user);

        $data = [
            'satellite_monitorable_type' => TerrafundProgramme::class,
            'satellite_monitorable_id' => $terrafundProgramme->id,
            'map' => $map,
            'alt_text' => 'Lorem ipsum dolor sit amet',
        ];
        $response = $this->actingAs($user)
            ->postJson('/api/satellite_monitor', $data);
        $this->actingAs($user)
            ->getJson('/api/satellite_monitor/terrafund_programme/'.$terrafundProgramme->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    private function uploadMap($user): int
    {
        $mapFile = new File('map.tiff', fopen(__DIR__ . '/../../resources/seeds/map.tiff', 'r'));

        $data = [
            'upload' => $mapFile,
        ];

        $response = $this->actingAs($user)->postJson('/api/uploads', $data, );
        $id = json_decode($response->getContent())->data->id;

        return $id;
    }
}
