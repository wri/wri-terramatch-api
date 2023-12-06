<?php

namespace App\Mail;

use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use Exception;

class ProjectUpdated extends Mail
{
    public function __construct(string $type, string $model, int $id)
    {
        switch ($type) {
            case 'Match':
                $this->subject = "A Project You've Matched With Has Changed";
                $this->title = "A Project You've Matched With Has Changed";

                break;
            case 'Interest':
                $this->subject = "A Project You're Interested In Has Changed";
                $this->title = "A Project You're Interested In Has Changed";

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
        $this->body =
            e($name) . ' has changed.<br><br>' .
            "You may want to review the changes to ensure you're still interested.<br><br>" .
            'Follow this link to view the project.';
        $this->link = $link;
        $this->cta = 'View Project';
    }
}
