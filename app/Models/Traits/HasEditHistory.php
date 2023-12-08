<?php

namespace App\Models\Traits;

use App\Models\EditHistory;

trait HasEditHistory
{
    public function editHistory()
    {
        return $this->morphMany(EditHistory::class, 'editable');
    }
}
