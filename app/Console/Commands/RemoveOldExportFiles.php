<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveOldExportFiles extends Command
{
    protected $signature = 'remove-export-files';

    protected $description = 'Removes working export files older than 1 day';

    public function handle(): int
    {
        collect(Storage::disk('public')->listContents('/temp', true))
        ->each(function ($file) {
            if ($file['type'] == 'file' && $file['lastModified'] < now()->subDays(1)->getTimestamp()) {
                Storage::disk('public')->delete($file['path']);
            }
        });

        return 0;
    }
}
