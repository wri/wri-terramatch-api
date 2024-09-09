<?php

namespace App\Mail;
use App\Models\V2\LocalizationKey;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

abstract class I18nMail extends Mail
{

    protected string $subjectKey;
    protected string $titleKey;
    protected string $bodyKey;
    protected string $ctaKey;
    protected string $userLocale;
    protected array $params;

    public function build() {
        if (isset($this->subjectKey)) {
            $this->subject = $this->getValueTranslated($this->subjectKey);
        }
        if (isset($this->titleKey)) {
            $this->title = $this->getValueTranslated($this->titleKey);
        }
        if (isset($this->bodyKey)) {
            $this->body = $this->getValueTranslated($this->bodyKey);
        }
        if (isset($this->ctaKey)) {
            $this->cta = $this->getValueTranslated($this->ctaKey);
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

    public function setUserLocale(string $location): I18nMail
    {
        $this->userLocale = $location;
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


    public function getValueTranslated($valueKey) 
    {
        App::setLocale($this->userLocale);
        $localizationKey = LocalizationKey::where('key', $valueKey)->first();
        if (!empty($this->params)) {
            return str_replace(array_keys($this->params), array_values($this->params), $localizationKey->translated_value);
        }
        return $localizationKey->translated_value;
    }
}