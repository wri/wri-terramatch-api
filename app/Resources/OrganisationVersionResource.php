<?php

namespace App\Resources;

use App\Models\Organisation as ParentModel;
use App\Models\OrganisationVersion as ChildModel;

class OrganisationVersionResource extends Resource
{
    public function __construct(ParentModel $parentModel, ChildModel $childModel)
    {
        $this->id = $childModel->id;
        $this->status = $childModel->status;
        $this->approved_rejected_by = $childModel->approved_rejected_by;
        $this->approved_rejected_at = $childModel->approved_rejected_at;
        $this->rejected_reason = $childModel->rejected_reason;
        $this->rejected_reason_body = $childModel->rejected_reason_body;
        $this->created_at = $childModel->created_at;
        $this->updated_at = $childModel->updated_at;
        $this->data = (object) [
            "id" => $parentModel->id,
            "name" => $childModel->name,
            "description" => $childModel->description,
            "address_1" => $childModel->address_1,
            "address_2" => $childModel->address_2,
            "city" => $childModel->city,
            "state" => $childModel->state,
            "zip_code" => $childModel->zip_code,
            "country" => $childModel->country,
            "phone_number" => $childModel->phone_number,
            "website" => $childModel->website,
            "type" => $childModel->type,
            "category" => $childModel->category,
            "facebook" => $childModel->facebook,
            "twitter" => $childModel->twitter,
            "linkedin" => $childModel->linkedin,
            "instagram" => $childModel->instagram,
            "avatar" => $childModel->avatar,
            "cover_photo" => $childModel->cover_photo,
            "video" => $childModel->video,
            "founded_at" => $childModel->founded_at,
            "created_at" => $parentModel->created_at,
            "updated_at" => $childModel->created_at
        ];
    }
}