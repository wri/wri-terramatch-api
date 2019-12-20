<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidUploadTypeException;
use App\Exceptions\UploadNotFoundException;
use App\Exceptions\DuplicateUploadException;

class Upload extends Model
{
    public const IMAGES = ["png", "jpg", "gif"];
    public const VIDEOS = ["mp4"];
    public const FILES = ["pdf"];
    public const IMAGES_FILES = ["png", "jpg", "gif", "pdf"];

    public $guarded = [];

    public function findByIdAndExtensionAndUserIdOrFail(?int $id, array $extensions, $userId): ?Upload
    {
        if (is_null($id)) {
            return null;
        }
        $upload = $this->find($id);
        if (is_null($upload)) {
            throw new UploadNotFoundException();
        }
        if (!in_array(pathinfo($upload->location, PATHINFO_EXTENSION), $extensions)) {
            throw new InvalidUploadTypeException();
        }
        if ($upload->user_id != $userId) {
            throw new UploadNotFoundException();
        }
        return $upload;
    }

    /**
     * This methods takes an arbitrary amount of upload models and asserts that
     * they are all unique. This is used to assert that the same upload isn't
     * being used twice in the same request.
     */
    public function assertUnique(?Upload ...$uploads): void
    {
        $ids = [];
        foreach ($uploads as $upload) {
            if (is_null($upload)) {
                continue;
            }
            $ids[] = $upload->id;
        }
        if (count($ids) != count(array_unique($ids))) {
            throw new DuplicateUploadException();
        }
    }
}
