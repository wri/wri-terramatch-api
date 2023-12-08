<?php

namespace App\Http\Controllers;

use App\Helpers\PPCExportHelper;
use App\Models\Site;
use Illuminate\Http\Request;

class PPCSiteExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('export', Site::class);

        $sites = $request->site_ids ? Site::whereIn('id', $request->site_ids)->get() : Site::all();

        $filename = public_path('storage/Site Export - ' . now() . '.zip');

        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);

        $sites->each(function (Site $site) use ($zip) {
            $siteZipName = public_path('storage/Site ' . $site->name . '(' . $site->id . ') Export - ' . now() . '.zip');
            PPCExportHelper::generateSiteFilesZip($siteZipName, $site);
            $zip->addFile($siteZipName, '/Site ' . $site->name . '(' . $site->id . ') Export.zip');
        });

        $csv = PPCExportHelper::generateSitesCsv($sites);
        $zip->addFromString('Site Export.csv', $csv->toString());

        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }
}
