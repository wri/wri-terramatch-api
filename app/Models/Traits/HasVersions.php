<?php

namespace App\Models\Traits;

trait HasVersions
{
    public function versions()
    {
        return $this->hasMany($this->versionClass);
    }

    public function approved_version()
    {
        return $this->hasOne($this->versionClass)->approved();
    }

    public function details()
    {
        return $this->hasMany($this->versionClass)->latest()->first();
    }
}
