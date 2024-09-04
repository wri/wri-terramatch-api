<?php

namespace App\Models\DTOs;

use App\Http\Resources\V2\Files\FileResource;
use App\Models\V2\User;

class AuditStatusDTO
{
    public $id;

    public $status;

    public $first_name;

    public $last_name;

    public $comment;

    public $type;

    public $request_removed;

    public $fileConfiguration;

    public $date_created;

    public $attachments;

    public function __construct(
        $id,
        $status,
        $first_name,
        $last_name,
        $comment,
        $type,
        $request_removed,
        $date_created,
        $attachments
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->comment = $comment;
        $this->type = $type;
        $this->request_removed = $request_removed;
        $this->date_created = $date_created;
        $this->attachments = $attachments;
    }

    public static function fromAudits($audit)
    {
        $user = self::getUserFromAudit($audit->user_id);

        return new AuditStatusDTO(
            $audit->id,
            $audit->status,
            $user ? $user->first_name : null,
            $user ? $user->last_name : null,
            $audit->new_values ? data_get($audit->new_values, 'feedback') : null,
            $audit->type,
            $audit->request_removed,
            $audit->created_at,
            []
        );
    }

    public static function fromAuditStatus($auditStatus)
    {
        $auditStatus->appendFilesToResource($auditStatus->toArray());

        return new AuditStatusDTO(
            $auditStatus->id,
            $auditStatus->status,
            $auditStatus->first_name,
            $auditStatus->last_name,
            $auditStatus->comment,
            $auditStatus->type,
            $auditStatus->request_removed,
            $auditStatus->date_created,
            FileResource::collection($auditStatus->getMedia('attachments'))
        );
    }

    private static function getUserFromAudit($user_id)
    {
        return User::where('id', $user_id)->first(['first_name', 'last_name']);
    }
}
