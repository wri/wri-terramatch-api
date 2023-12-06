<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class V2UpdateMediaFileTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-update-media-file-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update filetypes in media based on mime type';

    public function handle()
    {
        Media::chunk(500, function ($chunk) {
            foreach ($chunk as $media) {
                $documents = ['application/pdf', 'application/vnd.ms-excel', 'text/plain', 'application/msword'];
                $images = ['image/png', 'image/jpeg', 'image/svg+xml'];

                if (in_array($media->mime_type, $documents)) {
                    $media->file_type = 'documents';
                    $media->save();
                }

                if (in_array($media->mime_type, $images)) {
                    $media->file_type = 'media';
                    $media->save();
                }
            }
        });
    }
}
