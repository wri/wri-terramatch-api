<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;

final class SatelliteMonitorControllerTest extends TestCase
{
    use RefreshDatabase;

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
