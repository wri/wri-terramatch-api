<?php

namespace App\Http\Resources\V2\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => UserLiteResource::collection($this->collection)];
    }
}
