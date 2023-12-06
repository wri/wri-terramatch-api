<?php

namespace App\Http\Resources\V2\Organisation;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyOrganisationLiteResource extends JsonResource
{
    public function toArray($request)
    {
        $orRelation = DB::table('organisation_user')
            ->select('status')
            ->where('user_id', Auth::user()->id)
            ->where('organisation_id', $this->id)
            ->first();

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'users_status' => data_get($orRelation, 'status', 'na'),
            'type' => $this->type,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
