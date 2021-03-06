<?php

namespace App\Resources;

use App\Models\Organisation as ParentModel;
use App\Models\OrganisationVersion as ChildModel;

class OrganisationResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->name = $childModel->name ?? null;
        $this->description = $childModel->description ?? null;
        $this->address_1 = $childModel->address_1 ?? null;
        $this->address_2 = $childModel->address_2 ?? null;
        $this->city = $childModel->city ?? null;
        $this->state = $childModel->state ?? null;
        $this->zip_code = $childModel->zip_code ?? null;
        $this->country = $childModel->country ?? null;
        $this->phone_number = $childModel->phone_number ?? null;
        $this->website = $childModel->website ?? null;
        $this->type = $childModel->type ?? null;
        $this->category = $childModel->category ?? null;
        $this->facebook = $childModel->facebook ?? null;
        $this->twitter = $childModel->twitter ?? null;
        $this->linkedin = $childModel->linkedin ?? null;
        $this->instagram = $childModel->instagram ?? null;
        $this->avatar = $childModel->avatar ?? null;
        $this->cover_photo = $childModel->cover_photo ?? null;
        $this->video = $childModel->video ?? null;
        $this->founded_at = $childModel->founded_at ?? null;
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
    }
}