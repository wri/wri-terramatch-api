<?php

namespace App\Mail;
use App\Models\V2\LocalizationKey;
use Illuminate\Support\Facades\App;

abstract class I18nMail extends Mail
{

    protected string $subjectKey;
    protected string $titleKey;
    protected string $bodyKey;
    protected string $ctaKey;
    protected string $userLocation;
    protected array $params;

    public function build() {

        if (isset($this->subjectKey)) {
            $this->subject = $this->getValueTranslated($this->subjectKey, $this->userLocation, $this->params);
        }
        if (isset($this->titleKey)) {
            $this->title = $this->getValueTranslated($this->titleKey, $this->userLocation, $this->params);
        }
        if (isset($this->bodyKey)) {
            $this->body = $this->getValueTranslated($this->bodyKey, $this->userLocation, $this->params);
        }
        if (isset($this->ctaKey)) {
            $this->cta = $this->getValueTranslated($this->ctaKey, $this->userLocation, $this->params);
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

    public function setCta(string $key): I18nMail
    {
        $this->ctaKey = $key;
        return $this;
    }

    public function setParams(array $params = []): I18nMail
    {
        $this->params = $params;
        return $this;
    }

    public function getValueTranslated($valueKey, $userLocation, ?array $params = []) 
    {
        App::setLocale($userLocation);
        $localizationKey = LocalizationKey::where('key', $valueKey)->first();
        
        if (!empty($params)) {
            return str_replace(array_keys($params), array_values($params), $localizationKey->translated_value);
        } 

        return $localizationKey->translated_value;
    }
}
