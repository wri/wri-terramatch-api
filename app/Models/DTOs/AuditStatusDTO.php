<?php

namespace App\Models\DTOs;

use App\Http\Resources\V2\Files\FileResource;
use App\Models\V2\User;

class AuditStatusDTO
{
    public $id;

    public $uuid;

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
        $uuid,
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
        $this->uuid = $uuid;
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
        $comment = data_get($audit->new_values, 'status').': '.data_get($audit->new_values, 'feedback');

        return new AuditStatusDTO(
            $audit->id,
            $audit->uuid ?? null,
            $audit->new_values ? data_get($audit->new_values, 'status') : null,
            $user ? $user->first_name : null,
            $user ? $user->last_name : null,
            $audit->new_values ? str_replace('-', ' ', $comment) : null,
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
            $auditStatus->uuid,
            $auditStatus->status === 'started' ? 'Draft' : $auditStatus->status,
            $auditStatus->first_name,
            $auditStatus->last_name,
            $auditStatus->comment,
            $auditStatus->type,
            $auditStatus->request_removed,
            $auditStatus->created_at,
            FileResource::collection($auditStatus->getMedia('attachments'))
        );
    }

    private static function getUserFromAudit($user_id)
    {
        return User::where('id', $user_id)->first(['first_name', 'last_name']);
    }
}
