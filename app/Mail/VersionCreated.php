<?php

namespace App\Mail;

use App\Models\Organisation as OrganisationModel;
use App\Models\Pitch as PitchModel;
use Exception;

class VersionCreated extends Mail
{
    public function __construct(String $model, Int $id)
    {
        switch ($model) {
            case 'Organisation':
                $link = '/admin/organization/preview/' . $id;
                $versions = OrganisationModel::findOrFail($id)->versions;

                break;
            case 'Pitch':
                $link = '/admin/pitches/preview/' . $id;
                $versions = PitchModel::findOrFail($id)->versions;

                break;
            default:
                throw new Exception();
        }
        $statuses = ['pending', 'approved', 'rejected', 'archived'];
        foreach ($statuses as $status) {
            $version = $versions->where('status', '=', $status)->sortByDesc('created_at')->first();
            if (! is_null($version)) {
                break;
            }
        }
        if (is_null($version)) {
            throw new Exception();
        }
        $this->subject = 'CHANGES REQUIRING YOUR APPROVAL ';
        $this->title = 'Changes Requiring Your Approval';
        $this->body =
            'Changes have been made to ' . e($version->name) . '. Follow this link to review the changes.';
        $this->link = $link;
        $this->cta = 'Review Changes';
    }
}
