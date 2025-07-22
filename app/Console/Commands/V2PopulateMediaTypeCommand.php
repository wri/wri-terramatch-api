<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class V2PopulateMediaTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-populate-media-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the new type field in media table based on mime_type';

    public function handle()
    {
        Media::chunk(500, function ($chunk) {
            foreach ($chunk as $media) {
                $media->type = $this->mapType($media->mime_type);
                $media->save();
            }
        });
        $this->info('Media type field populated successfully.');
    }

    private function mapType($mime)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/csv' => 'csv',
            'text/plain' => 'txt',
        ];

        if (isset($map[$mime])) {
            return $map[$mime];
        }

        if (strpos($mime, '/') !== false) {
            return substr($mime, strpos($mime, '/') + 1);
        }

        return 'other';
    }
}
