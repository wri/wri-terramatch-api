<?php

namespace App\Mail;

use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use Exception;

class ProjectUpdated extends I18nMail
{
    public function __construct(string $type, string $model, int $id, $user)
    {
        parent::__construct($user);
        switch ($type) {
            case 'Match':
                $this->setSubjectKey('project-updated.subject-match')
                    ->setTitleKey('project-updated.title-match');

                break;
            case 'Interest':
                $this->setSubjectKey('project-updated.subject-interest')
                    ->setTitleKey('project-updated.title-interest');

                break;
            default:
                throw new Exception();
        }
        switch ($model) {
            case 'Offer':
                $offer = OfferModel::findOrFail($id);
                $name = $offer->name;
                $link = '/funding/' . $id;

                break;
            case 'Pitch':
                $pitch = PitchModel::findOrFail($id);
                $versions = $pitch->versions;
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
                $name = $version->name;
                $link = '/projects/' . $id;

                break;
            default:
                throw new Exception();
        }
        $this->setBodyKey('project-updated.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('project-updated.cta');
        $this->link = $link;
    }
}
