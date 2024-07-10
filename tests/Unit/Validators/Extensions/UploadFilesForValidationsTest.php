<?php

namespace Tests\Feature;

use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadFilesForValidationsTest extends TestCase
{
  public const FILES_DIR = 'tests/Unit/Validators/Extensions/Polygons/TestFiles/';
  public function test_upload_shapefile_validate()
  {
      $passFilePath = self::FILES_DIR . 'validate_shapefile_csv.zip';
      $controller = new TerrafundCreateGeometryController();
      $file = new UploadedFile(
        $passFilePath,
        'validate_shapefile_csv.zip',
        'application/zip',
        null,
        true
    );

      $request = Request::create(
        '/api/v2/terrafund/upload-shapefile-validate',
        'POST',
        ['file' => $file],
        [],
        ['file' => $file]
    );

      $response = $controller->uploadShapefileWithValidation($request);

      $response->assertStatus(200);
      $response->assertHeader('Content-Type', 'text/csv');
  }

}
