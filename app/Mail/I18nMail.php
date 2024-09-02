<?php

namespace App\Mail;
use App\Models\V2\LocalizationKey;
use App\Models\V2\I18n\I18nTranslation;

abstract class I18nMail extends Mail
{
    protected string $subjectKey;
    protected string $titleKey;
    protected string $bodyKey;
    protected string $userLocation;

    public function build() {
        if (isset($this->subjectKey)) {
            $this->subject = $this->getValueTranslated($this->subjectKey, $this->userLocation);
        }
        if (isset($this->titleKey)) {
            $this->title = $this->getValueTranslated($this->titleKey, $this->userLocation);
        }
        if (isset($this->bodyKey)) {
            $this->body = $this->getValueTranslated($this->bodyKey, $this->userLocation);
        }
        parent::build();
    }

    public function setSubjectKey(string $key): I18nMail
    {
        $this->subjectKey = $key;
        return $this;
    }

    public function setTitleKey(string $key): I18nMail
    {
        $this->titleKey = $key;
        return $this;
    }

    public function setBodyKey(string $key): I18nMail
    {
        $this->bodyKey = $key;
        return $this;
    }

    public function setUserLocation(string $location): I18nMail
    {
        $this->userLocation = $location;
        return $this;
    }

    public function getValueTranslated($valueKey, $userLocation) 
    {
        $localizationKey = LocalizationKey::where('key', $valueKey)->first();
        // $i18item = I18nItem::where('id', $localizationKey->value_id)->first();
        $valueTranslation = I18nTranslation::where('i18n_item_id', $localizationKey->value_id)->where('language', $userLocation)->first();
        return $valueTranslation->getValue();
    }
}
