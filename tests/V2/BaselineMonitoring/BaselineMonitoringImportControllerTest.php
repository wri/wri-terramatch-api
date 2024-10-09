<?php

namespace Tests\V2\BaselineMonitoring;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BaselineMonitoringImportControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testInvokeAction(): void
    {
        $admin = User::factory()->admin()->create();
        $project = TerrafundProgramme::factory()->create();
        $data = [
            'Total Hectares Under Restoration,Agroforestry,Tree Planting/Reforestation,Direct Seeding,Tree Count,Tree Cover,Tree Cover Loss (2001-2021),Number of Ecosystem services restoration partners,carbon benefits,Tree count (field),Number of trees naturally regenerating,Percent survival of planted trees',
            '57,16,23,8,453,73,18,3,65,386,84,69',
            ];

        Storage::fake('imports');
        $file = UploadedFile::fake()->createWithContent('document.csv', implode(chr(10),  $data));

        $payload = [
            'importable_type' => 'terrafund_programme',
            'importable_id' => $project->id,
            'upload_file' => $file
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/imports/baseline-monitoring', $payload)
            ->assertStatus(200);

        $metric = $project->baselineMonitoring->first();
        $this->assertEquals($metric->tree_count,453);
        $this->assertEquals($metric->tree_cover,73);
        $this->assertEquals($metric->tree_cover_loss,18);
        $this->assertEquals($metric->carbon_benefits, 65);
        $this->assertEquals($metric->number_of_esrp, 3);
    }
}
