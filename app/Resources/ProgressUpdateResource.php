<?php

namespace App\Resources;

use App\Models\ProgressUpdate as ProgressUpdateModel;
use App\Models\V2\User as UserModel;

class ProgressUpdateResource extends Resource
{
    public function __construct(ProgressUpdateModel $progressUpdate)
    {
        $this->id = $progressUpdate->id;
        $this->monitoring_id = $progressUpdate->monitoring_id;
        $this->grouping = $progressUpdate->grouping;
        $this->title = $progressUpdate->title;
        $this->breakdown = $progressUpdate->breakdown;
        $this->summary = $progressUpdate->summary;
        $this->data = (object) $progressUpdate->data;
        $this->images = $progressUpdate->images;
        $this->created_at = $progressUpdate->created_at;
        $this->created_by = $progressUpdate->created_by;
        $user = UserModel::findOrFail($this->created_by);
        $this->created_by_admin = $user->isAdmin;
        $this->updated_at = $progressUpdate->updated_at;
    }
}
