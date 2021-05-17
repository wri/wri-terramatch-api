<?php

namespace App\Helpers;

use App\Exceptions\InvalidUploadTypeException;
use App\Exceptions\UploadNotFoundException;
use App\Exceptions\DuplicateUploadException;
use App\Models\Upload as UploadModel;

class UploadHelper
{
    public const MAX_FILESIZES = [
        "image/jpeg" => 2000,
        "image/png" => 2000,
        "image/gif" => 2000,
        "application/pdf" => 4000,
        "video/mp4" => 32000,
        "video/quicktime" => 32000,
        "video/3gpp" => 32000,
        "image/tiff" => 8000
    ];

    private function __construct()
    {
    }

    public const MAPS = ["tiff"];
    public const IMAGES = ["png", "jpg", "gif"];
    public const VIDEOS = ["mp4", "mov", "3gp"];
    public const FILES = ["pdf"];
    public const IMAGES_FILES = ["png", "jpg", "gif", "pdf"];

    /**
     * This method is a shorthand for translating a (potentially invalid) user
     * submitted upload ID into an authorised upload model of a specific type.
     */
    public static function findByIdAndValidate(?Int $id, Array $extensions, Int $userId): ?UploadModel
    {
        if (is_null($id)) {
            return null;
        }
        $upload = UploadModel::find($id);
        if (is_null($upload)) {
            throw new UploadNotFoundException();
        }
        $extension = pathinfo($upload->location, PATHINFO_EXTENSION);
        if (!in_array($extension, $extensions)) {
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
    public static function assertUnique(?UploadModel ...$uploads): Void
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
