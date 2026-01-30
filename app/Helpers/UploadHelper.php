<?php

namespace App\Helpers;

use App\Exceptions\DuplicateUploadException;
use App\Exceptions\InvalidUploadTypeException;
use App\Exceptions\UploadNotFoundException;
use App\Models\Upload as UploadModel;
use App\Models\V2\User;

class UploadHelper
{
    public const MAX_FILESIZES = [
        'image/jpeg' => 10000,
        'image/png' => 10000,
        'image/gif' => 10000,
        'image/heif' => 10000,
        'image/heic' => 10000,
        'application/pdf' => 10000,
        'application/msword' => 10000,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 10000,
        'video/mp4' => 32000,
        'video/quicktime' => 32000,
        'video/3gpp' => 32000,
        'image/tiff' => 10000,
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 10000,
        'application/vnd.ms-excel' => 10000,
        'text/plain' => 10000,
        'text/csv' => 10000,
    ];

    private function __construct()
    {
    }

    /**
     * If this list gets any larger, or more variations required, refactoring might
     * want to be considered.
     */
    public const MAPS = ['tiff'];
    public const IMAGES = ['png', 'jpg', 'gif', 'heif', 'heic'];
    public const VIDEOS = ['mp4', 'mov', '3gp'];
    public const FILES = ['pdf', 'xlsx', 'xls', 'csv', 'text', 'doc'];
    public const FILES_CSV = ['csv'];
    public const FILES_PDF = ['pdf'];
    public const FILES_DOC_PDF = ['pdf', 'doc', 'docx'];
    public const IMAGES_FILES = ['png', 'jpg', 'gif', 'pdf', 'heif', 'heic'];
    public const IMAGES_VIDEOS = ['png', 'jpg', 'gif', 'mp4', 'mov', '3gp', 'heif', 'heic'];
    public const FILES_IMAGES = ['pdf', 'xlsx', 'xls', 'png', 'jpg', 'docx', 'doc', 'heif', 'heic'];

    /**
     * This method is a shorthand for translating a (potentially invalid) user
     * submitted upload ID into an authorised upload model of a specific type.
     */
    public static function findByIdAndValidate(?Int $id, array $extensions, Int $userId): ?UploadModel
    {
        if (is_null($id)) {
            return null;
        }
        $upload = UploadModel::find($id);
        if (is_null($upload)) {
            throw new UploadNotFoundException();
        }
        $extension = pathinfo($upload->location, PATHINFO_EXTENSION);
        if (! in_array($extension, $extensions)) {
            throw new InvalidUploadTypeException();
        }
        $uploadUser = User::where('id', $upload->user_id)->first();
        $user = User::where('id', $userId)->first();
        if ($user->organisation_id !== $uploadUser->organisation_id) {
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
