<?php

namespace App\Mail;

use App\Models\Organisation as OrganisationModel;
use App\Models\Pitch as PitchModel;
use Exception;

class VersionRejected extends Mail
{
    public function __construct(String $model, Int $id, String $explanation)
    {
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
        $this->subject = 'Your Changes Have Been Rejected';
        $this->title = 'Your Changes Have Been Rejected';
        $this->body = 'Your changes to ' . e($version->name) . ' have been rejected. ' . e($explanation) . '. Follow this link to view the changes.<br><br>' .
            'If you have any questions, feel free to message us at info@terramatch.org.';
        $this->link = $link;
        $this->cta = 'View Changes';
    }
}
