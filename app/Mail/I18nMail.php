<?php

namespace App\Mail;
use App\Models\V2\LocalizationKey;

abstract class I18nMail extends Mail
{
    protected string $subjectKey;
    protected string $titleKey;
    protected string $bodyKey;

    public function __construct()
    {
        if (isset($this->subjectKey)) {
            $this->subject = LocalizationKey::where('key', $this->subjectKey)->firstOrFail();
        }
        if (isset($this->titleKey)) {
            $this->title = LocalizationKey::where('key', $this->titleKey)->firstOrFail();
        }
        if (isset($this->bodyKey)) {
            $this->body = LocalizationKey::where('key', $this->bodyKey)->firstOrFail();
        }
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

}
