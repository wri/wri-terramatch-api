<?php

namespace App\Mail;

use App\Models\Organisation as OrganisationModel;
use App\Models\Pitch as PitchModel;
use Exception;

class VersionApproved extends I18nMail
{
    public function __construct(String $model, Int $id, $user)
    {
        parent::__construct($user);
        switch ($model) {
            case 'Organisation':
                $link = '/profile';
                $versions = OrganisationModel::findOrFail($id)->versions;

                break;
            case 'Pitch':
                $link = '/profile/projects/' . $id;
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
        $this->setSubjectKey('version-approved.subject')
            ->setTitleKey('version-approved.title')
            ->setBodyKey('version-approved.body')
            ->setParams(['{versionName}' => e($version->name)])
            ->setCta('version-approved.cta');
        $this->link = $link;
    }
}
