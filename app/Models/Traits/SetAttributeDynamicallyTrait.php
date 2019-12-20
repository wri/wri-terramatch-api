<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\App;
use Exception;

/**
 * This trait contains the logic for associating uploaded documents with an
 * attribute. By passing in either a string, an upload model, or a null value
 * we can ensure all uploads are associated correctly. This trait is used as
 * follows:
 *
 *         use SetAttributeDynamicallyTrait;
 *         public function setAvatarAttribute($avatar): void
 *         {
 *             $this->setAttributeDynamically("avatar", $avatar);
 *         }
 */
trait SetAttributeDynamicallyTrait
{
    public function setAttributeDynamically($name, $value)
    {
        $fileService = App::make("App\\Services\\FileService");
        if (is_object($value) && get_class($value) == "App\\Models\\Upload") {
            $currentValue = $this->attributes[$name] ?? null;
            if (!is_null($currentValue)) {
                $fileService->delete($currentValue);
            }
            $this->attributes[$name] = $value->location;
            $value->delete();
        } else if (is_string($value) && (substr($value, 0, 7) == "http://" || substr($value, 0, 8) == "https://")) {
            $currentValue = $this->attributes[$name] ?? null;
            if (!is_null($currentValue)) {
                $fileService->delete($currentValue);
            }
            $this->attributes[$name] = $fileService->copy($value);
        } else if (is_null($value)) {
            $currentValue = $this->attributes[$name] ?? null;
            if (!is_null($currentValue)) {
                $fileService->delete($currentValue);
            }
            $this->attributes[$name] = $value;
        } else {
            throw new Exception();
        }
    }
}