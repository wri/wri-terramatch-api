<?php

namespace App\Mail;

use App\Models\Organisation as OrganisationModel;
use App\Models\Pitch as PitchModel;
use Exception;
use Illuminate\Support\Facades\Auth;

class VersionCreated extends I18nMail
{
    public function __construct(String $model, Int $id)
    {
        $user = Auth::user();
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
        $this->setSubjectKey('version-created.subject')
            ->setTitleKey('version-created.title')
            ->setBodyKey('version-created.body')
            ->setParams(['{versionName}' => e($version->name)])
            ->setCta('version-created.cta')
            ->setUserLocale($user->locale);
        $this->link = $link;
    }
}
