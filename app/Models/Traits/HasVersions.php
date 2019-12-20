<?php

namespace App\Models\Traits;

trait HasVersions
{
    public function versions()
    {
        return $this->hasMany($this->versionClass);
    }

    public function approvedVersion()
    {
        return $this->hasOne($this->versionClass)->approved();
    }
}
