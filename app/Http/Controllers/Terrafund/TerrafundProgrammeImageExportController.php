<?php

namespace App\Http\Controllers\Terrafund;

use App\Exceptions\Terrafund\NoProgrammeFilesException;
use App\Helpers\TerrafundExportHelper;
use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TerrafundProgrammeImageExportController extends Controller
{
    public function __invoke(TerrafundProgramme $terrafundProgramme): BinaryFileResponse
    {
        $this->authorize('exportOwned', $terrafundProgramme);

        $files = TerrafundExportHelper::generateProgrammeImagesZip($terrafundProgramme);

        $filename = public_path('storage/Terrafund Programme Image Export - ' . now() . '.zip');

        $zip = new \ZipArchive();
        $zippedFiles = [];
        $zip->open($filename, \ZipArchive::CREATE);
        $files->each(function ($file) use (&$zip, &$zippedFiles) {
            $path = $this->getFileablePath($file);
            $extension = pathinfo($file->upload, PATHINFO_EXTENSION);
            if (in_array($extension, UploadHelper::IMAGES)) {
                $zippedFiles[] = $file->upload;
                $zip->addFromString($path, file_get_contents($file->upload));
            }
        });
        if (! count($zippedFiles)) {
            throw new NoProgrammeFilesException();
        }
        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }

    private function getFileablePath($fileable)
    {
        switch ($fileable->fileable_type) {
            case TerrafundProgramme::class:
                $programme = $fileable->fileable;
                $folder = $programme->name . ' (' . $programme->id . ')';

                break;
            case TerrafundProgrammeSubmission::class:
                $programmeSubmission = $fileable->fileable;
                $programme = $programmeSubmission->terrafundProgramme;
                $folder = $programme->name . ' (' . $programme->id . ')/submissions/' . $fileable->fileable_id;

                break;
            case TerrafundSite::class:
                $site = $fileable->fileable;
                $programme = $site->terrafundProgramme;
                $folder = $programme->name . ' (' . $programme->id . ')/sites/' . $site->name . ' (' . $site->id . ')';

                break;
            case TerrafundSiteSubmission::class:
                $siteSubmission = $fileable->fileable;
                $site = $siteSubmission->terrafundSite;
                $programme = $site->terrafundProgramme;
                $folder = $programme->name . ' (' . $programme->id . ')/sites/' . $site->name . ' (' . $site->id . ')/submissions' . $siteSubmission->id;

                break;
            case TerrafundNursery::class:
                $nursery = $fileable->fileable;
                $programme = $nursery->terrafundProgramme;
                $folder = $programme->name . ' (' . $programme->id . ')/nurseries/' . $nursery->name . ' (' . $nursery->id . ')';

                break;
            case TerrafundNurserySubmission::class:
                $nurseySubmission = $fileable->fileable;
                $nursery = $nurseySubmission->terrafundNursery;
                $programme = $nursery->terrafundProgramme;
                $folder = $programme->name . ' (' . $programme->id . ')' . '/nurseries/' . $nursery->name . ' (' . $nursery->id . ')/submissions' . $nurseySubmission->id;

                break;
        }

        return $folder . '/' . basename($fileable->upload);
    }
}
