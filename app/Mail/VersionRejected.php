<?php

namespace App\Mail;

use App\Models\Organisation as OrganisationModel;
use App\Models\Pitch as PitchModel;
use Exception;
use Illuminate\Support\Facades\Auth;

class VersionRejected extends I18nMail
{
    public function __construct(String $model, Int $id, String $explanation)
    {
        $user = Auth::user();
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
        $this->setSubjectKey('version-rejected.subject')
            ->setTitleKey('version-rejected.title')
            ->setBodyKey('version-rejected.body')
            ->setParams(['{versionName}' => e($version->name), '{explanation}' => e($explanation)])
            ->setCta('version-rejected.cta')
            ->setUserLocation($user->locale);
        $this->link = $link;
    }
}
