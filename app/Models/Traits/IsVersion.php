<?php

namespace App\Models\Traits;

trait IsVersion
{
    protected $parentForeignKey;

    public function getParentForeignKey()
    {
        if (is_null($this->parentForeignKey)) {
            $this->parentForeignKey = app($this->parentClass)->getForeignKey();
        }

        return $this->parentForeignKey;
    }

    public function parent()
    {
        return $this->belongsTo($this->parentClass, $this->getParentForeignKey());
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }
}
