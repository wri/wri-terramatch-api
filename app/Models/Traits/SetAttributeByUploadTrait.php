<?php

namespace App\Models\Traits;

use App\Models\ElevatorVideo as ElevatorVideoModel;
use App\Models\Upload as UploadModel;
use Exception;
use Illuminate\Support\Facades\App;

/**
 * This trait contains the logic for associating uploads with an
 * attribute. By passing in either a string, an upload model, or a null value
 * we can ensure all uploads are associated correctly. This trait is used as
 * follows:
 *
 *         use SetAttributeByUploadTrait;
 *         public function setAvatarAttribute($avatar)
 *         {
 *             $this->setAttributeByUpload("avatar", $avatar);
 *         }
 */
trait SetAttributeByUploadTrait
{
    public function setAttributeByUpload($name, $value)
    {
        $fileService = App::make(\App\Services\FileService::class);
        if (is_object($value) && get_class($value) == \App\Models\Upload::class) {
            $currentValue = $this->attributes[$name] ?? null;
            if (! is_null($currentValue)) {
                $fileService->delete($currentValue);
            }
            $this->attributes[$name] = $value->location;
            ElevatorVideoModel::where('upload_id', '=', $value->id)->delete();
            UploadModel::where('id', '=', $value->id)->delete();
        } elseif (is_string($value) && (substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://')) {
            $currentValue = $this->attributes[$name] ?? null;
            $this->attributes[$name] = $fileService->copy($value);
            if (! is_null($currentValue)) {
                $fileService->delete($currentValue);
            }
        } elseif (is_null($value)) {
            $currentValue = $this->attributes[$name] ?? null;
            if (! is_null($currentValue)) {
                $fileService->delete($currentValue);
            }
            $this->attributes[$name] = null;
        } else {
            throw new Exception();
        }
    }
}
