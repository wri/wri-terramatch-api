<?php

namespace App\Console\Commands;

use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UploadShapefileCommand extends Command
{
    protected $signature = 'shapefile:upload {file} {--site_uuid=} {--submit_polygon_loaded=}';

    protected $description = 'Upload a shapefile to the application';

    public function handle()
    {
        $filePath = $this->argument('file');
        $siteUuid = $this->option('site_uuid');
        $submitPolygonLoaded = $this->option('submit_polygon_loaded');

        if (! file_exists($filePath)) {
            $this->error("File not found: $filePath");

            return 1;
        }

        // Create a fake UploadedFile instance
        $uploadedFile = new UploadedFile(
            $filePath,
            basename($filePath),
            mime_content_type($filePath),
            null,
            true // Set test mode to true to prevent the file from being moved
        );

        $request = new Request();
        $request->files->set('file', $uploadedFile);
        $request->merge([
          'uuid' => $siteUuid,
          'submit_polygon_loaded' => $submitPolygonLoaded,
        ]);
        $controller = new TerrafundCreateGeometryController();
        $response = $controller->uploadShapefile($request);

        // Handle the response
        if ($response->getStatusCode() === 200) {
            $this->info('Shapefile uploaded successfully: ' . $response->getContent());

            return 0;
        } else {
            $this->error('Failed to upload shapefile: ' . $response->getContent());

            return 1;
        }
    }
}
