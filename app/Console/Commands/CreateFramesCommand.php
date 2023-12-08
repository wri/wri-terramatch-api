<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class CreateFramesCommand extends Command
{
    protected $signature = 'create-frames';

    protected $description = 'Creates the frames used by the elevator videos';

    public function handle(): int
    {
        $s3Client = App::make('CustomS3Client');
        $directory = __DIR__ . '/../../../resources/frames';
        $frames = ['introduction.mp4', 'aims.mp4', 'importance.mp4'];
        foreach ($frames as $frame) {
            $s3Client->putObject([
                'Bucket' => config('app.s3.bucket'),
                'Key' => $frame,
                'Body' => file_get_contents($directory . '/' . $frame),
                'ACL' => 'public-read',
                'ContentType' => 'video/mp4',
            ]);
        }

        return 0;
    }
}
