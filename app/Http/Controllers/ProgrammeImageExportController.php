<?php

namespace App\Http\Controllers;

use App\Exceptions\Terrafund\NoProgrammeFilesException;
use App\Helpers\PPCExportHelper;
use App\Helpers\UploadHelper;
use App\Models\Programme;

class ProgrammeImageExportController extends Controller
{
    public function __invoke(Programme $programme)
    {
        $this->authorize('exportOwned', $programme);

        $collection['a'] = PPCExportHelper::getProgrammeAndSiteImages($programme);
        $collection['b'] = PPCExportHelper::getProgrammeAndSiteSubmissionImages($programme);

        $filename = public_path('storage/PPC Programme Image Export - ' . now() . '.zip');

        $zip = new \ZipArchive();
        $zippedFiles = [];
        $zip->open($filename, \ZipArchive::CREATE);
        foreach ($collection as $files) {
            $files->each(function ($file) use (&$zip, &$zippedFiles) {
                $path = $this->getFileablePath($file);
                $extension = pathinfo($file->upload, PATHINFO_EXTENSION);
                if (in_array($extension, UploadHelper::IMAGES)) {
                    $zippedFiles[] = $file->upload;
                    $zip->addFromString($path, file_get_contents($file->upload));
                }
            });
        }

        if (! count($zippedFiles)) {
            throw new NoProgrammeFilesException();
        }
        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }

    private function getFileablePath($fileable)
    {
        if (! empty($fileable->programme_id)) {
            $folder = $fileable->programme->name . ' (' . $fileable->programme->id . ')';
        } elseif (! empty($fileable->site_id)) {
            $site = $fileable->site;
            $programme = $site->programme;
            $folder = $programme->name . ' (' . $programme->id . ')/sites/' . $site->name . ' (' .$site->id . ')';
        } elseif (! empty($fileable->site_submission_id)) {
            $siteSubmission = $fileable->siteSubmission;
            $site = $siteSubmission->site;
            $programme = $site->programme;
            $folder = $programme->name . ' (' . $programme->id . ')/sites/' . $site->name . ' (' . $site->id . ')/submissions/' . $siteSubmission->id;
        } elseif (! empty($fileable->submission_id)) {
            $submission = $fileable->submission;
            $programme = $submission->programme;
            $folder = $programme->name . ' (' . $programme->id . ')/submissions/' . $submission->id;
        }

        return $folder . '/' . basename($fileable->upload);
    }
}
