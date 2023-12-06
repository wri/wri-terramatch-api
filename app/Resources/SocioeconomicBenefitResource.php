<?php

namespace App\Resources;

use App\Models\SocioeconomicBenefit as SocioeconomicBenefitModel;

class SocioeconomicBenefitResource extends Resource
{
    public function __construct(SocioeconomicBenefitModel $socioeconomicBenefit)
    {
        $this->id = $socioeconomicBenefit->id;
        $this->upload = $socioeconomicBenefit->upload;
        $this->upload_name = $socioeconomicBenefit->name;
        $this->programme_id = $socioeconomicBenefit->programme_id;
        $this->programme_submission_id = $socioeconomicBenefit->programme_submission_id;
        $this->site_id = $socioeconomicBenefit->site_id;
        $this->site_submission_id = $socioeconomicBenefit->site_submission_id;
        $this->uploaded_at = $socioeconomicBenefit->created_at;
    }
}
