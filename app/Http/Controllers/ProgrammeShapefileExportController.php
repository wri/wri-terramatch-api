<?php

namespace App\Http\Controllers;

use App\Models\Programme;

class ProgrammeShapefileExportController extends Controller
{
    private $files = [];

    private $counter = 0;

    public function __invoke(Programme $programme)
    {
        $this->authorize('exportOwned', $programme);

        $zipFilename = public_path('storage/PPC Programme Shapefile Export - ' . now() . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zipFilename, \ZipArchive::CREATE);

        foreach ($programme->sites as $site) {
            if (! empty($site->boundary_geojson)) {
                $zip->addFile($this->makeFile($site->boundary_geojson), "$site->name ($site->id).geojson");
                $this->counter++;
            }
        }

        if (! empty($programme->boundary_geojson)) {
            $zip->addFile($this->makeFile($programme->boundary_geojson), "$programme->name ($programme->id).geojson");
        }

        $zip->close();

        return response()->download($zipFilename)->deleteFileAfterSend();
    }

    private function makeFile(string $geojson): string
    {
        $this->files[$this->counter] = tmpfile();
        fwrite($this->files[$this->counter], $geojson);

        return stream_get_meta_data($this->files[$this->counter])['uri'];
    }
}
