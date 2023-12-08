<?php

namespace App\Http\Resources\V2\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LimitedUsersCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => LimitedUserResource::collection($this->collection)];
    }
}
