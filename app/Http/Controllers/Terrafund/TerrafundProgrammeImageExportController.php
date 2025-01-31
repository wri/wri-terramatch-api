<?php

namespace App\Http\Controllers\Terrafund;

use App\Exceptions\Terrafund\NoProgrammeFilesException;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TerrafundProgrammeImageExportController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        $filename = public_path('storage/Terrafund Programme Image Export - ' . now() . '.zip');

        $zip = new \ZipArchive();
        $zippedFiles = [];
        $zip->open($filename, \ZipArchive::CREATE);
        if (! count($zippedFiles)) {
            throw new NoProgrammeFilesException();
        }
        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }
}
