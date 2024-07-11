<?php

namespace Tests\Feature;

use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
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

      $this->assertTrue($response->status() === 200, "Expected status 200, got {$response->status()}");
      $this->assertTrue($response->headers->get('Content-Type') === 'text/csv', "Expected Content-Type 'text/csv', got {$response->headers->get('Content-Type')}");
  }
  public function test_upload_geojson_validate()
  {
      $passFilePath = self::FILES_DIR . 'validate_geojson_csv.geojson';
      $controller = new TerrafundCreateGeometryController();
      $file = new UploadedFile(
        $passFilePath,
        'validate_geojson_csv.geojson',
        'application/geo+json',
        null,
        true
      );

      $request = Request::create(
        '/api/v2/terrafund/upload-geojson-validate',
        'POST',
        ['file' => $file],
        [],
        ['file' => $file]
    );

      $response = $controller->uploadGeoJSONFileWithValidation($request);

      $this->assertTrue($response->status() === 200, "Expected status 200, got {$response->status()}");
      $this->assertTrue($response->headers->get('Content-Type') === 'text/csv', "Expected Content-Type 'text/csv', got {$response->headers->get('Content-Type')}");
  }
  public function test_upload_kml_validate()
  {
      $passFilePath = self::FILES_DIR . 'validate_kml_csv.kml';
      $controller = new TerrafundCreateGeometryController();
      $file = new UploadedFile(
        $passFilePath,
        'validate_kml_csv.zip',
        'application/vnd.google-earth.kml+xml',
        null,
        true
    );

      $request = Request::create(
        '/api/v2/terrafund/upload-kml-validate',
        'POST',
        ['file' => $file],
        [],
        ['file' => $file]
    );

      $response = $controller->uploadKMLFileWithValidation($request);

      $this->assertTrue($response->status() === 200, "Expected status 200, got {$response->status()}");
      $this->assertTrue($response->headers->get('Content-Type') === 'text/csv', "Expected Content-Type 'text/csv', got {$response->headers->get('Content-Type')}");
  }
} 
