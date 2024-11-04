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

    protected string $userLocale;

    protected array $params = [];

    protected array $subjectParams;

    protected array $bodyParams;

    protected array $titleParams;

    public function __construct($user)
    {
        $this->userLocale = is_null($user) ? 'en-US' : $user->locale ?? $user['locale'] ?? 'en-US';
    }

    public function build()
    {
        if (isset($this->subjectKey)) {
            $this->subject = $this->getValueTranslated($this->subjectKey, $this->subjectParams ?? $this->params);
        }
        if (isset($this->titleKey)) {
            $this->title = $this->getValueTranslated($this->titleKey, $this->titleParams ?? $this->params);
        }
        if (isset($this->bodyKey)) {
            $this->body = $this->getValueTranslated($this->bodyKey, $this->bodyParams ?? $this->params);
        }
        if (isset($this->ctaKey)) {
            $this->cta = $this->getValueTranslated($this->ctaKey, $this->params);
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

    public function setCta(string $key): I18nMail
    {
        $this->ctaKey = $key;

        return $this;
    }

    public function setSubjectParams(array $params = []): I18nMail
    {
        $this->subjectParams = $params;

        return $this;
    }

    public function setBodyParams(array $params = []): I18nMail
    {
        $this->bodyParams = $params;

        return $this;
    }

    public function setTitleParams(array $params = []): I18nMail
    {
        $this->titleParams = $params;

        return $this;
    }

    public function setParams(array $params = []): I18nMail
    {
        $this->params = $params;

        return $this;
    }

    public function getValueTranslated($valueKey, $params)
    {
        App::setLocale($this->userLocale ?? 'en-US');
        $localizationKey = LocalizationKey::where('key', $valueKey)->first();
        if (is_null($localizationKey)) {
            return $valueKey;
        }

        if (! empty($params)) {
            return str_replace(array_keys($params), array_values($params), $localizationKey->translated_value);
        }

        return $localizationKey->translated_value;
    }
}
