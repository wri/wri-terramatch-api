<?php

namespace App\Http\Controllers;

use App\Models\Terrafund\TerrafundProgramme;

class TerrafundProgrammeShapefileExportController extends Controller
{
    private $files = [];

    private $counter = 0;

    public function __invoke(TerrafundProgramme $terrafundProgramme)
    {
        $this->authorize('exportOwned', $terrafundProgramme);

        $zipFilename = public_path('storage/Terrafund Programme Shapefile Export - ' . now() . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zipFilename, \ZipArchive::CREATE);

        foreach ($terrafundProgramme->terrafundSites as $site) {
            if (! empty($site->boundary_geojson)) {
                $zip->addFile($this->makeFile($site->boundary_geojson), "$site->name ($site->id).geojson");
                $this->counter++;
            }
        }

        if (! empty($terrafundProgramme->boundary_geojson)) {
            $zip->addFile($this->makeFile($terrafundProgramme->boundary_geojson), "$terrafundProgramme->name ($terrafundProgramme->id).geojson");
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
