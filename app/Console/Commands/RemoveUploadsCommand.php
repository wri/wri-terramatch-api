<?php

namespace App\Console\Commands;

use App\Services\FileService;
use Illuminate\Console\Command;
use App\Models\Upload as UploadModel;
use DateTime;
use DateTimeZone;
use Exception;

class RemoveUploadsCommand extends Command
{
    protected $signature = "remove-uploads";
    protected $description = "Removes uploads older than 1 hour";

    private $uploadModel = null;
    private $fileService = null;

    public function __construct(UploadModel $uploadModel, FileService $fileService)
    {
        parent::__construct();
        $this->uploadModel = $uploadModel;
        $this->fileService = $fileService;
    }

    public function handle()
    {
        $past = new DateTime("now - 1 hour", new DateTimeZone("UTC"));
        $uploads = $this->uploadModel->where("created_at", "<=", $past)->get();
        foreach ($uploads as $upload) {
            $this->fileService->delete($upload->location);
            $upload->delete();
        }
    }
}
