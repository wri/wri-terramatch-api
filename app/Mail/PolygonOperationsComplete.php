<?php

namespace App\Mail;

class PolygonOperationsComplete extends I18nMail
{
    protected $site;

    protected $operation;

    protected $completedAt;

    public function __construct($site, $operation, $user, $completedAt)
    {
        parent::__construct($user);

        $this->site = $site;
        $this->operation = $operation;
        $this->completedAt = $completedAt;

        $this->setSubjectKey('polygon-validation.subject')
            ->setTitleKey('polygon-validation.title')
            ->setBodyKey('polygon-validation.body')
            ->setParams([
                '{operation}' => e($operation),
                '{operationUpper}' => strtoupper(e($operation)),
                '{siteName}' => e($site->name),
                '{completedTime}' => $completedAt->format('H:i'),
            ])
            ->setCta('polygon-validation.cta');

        if ($user->hasRole('project-developer')) {
            $this->link = '/site/' . $site->uuid;
        } else {
            $this->link = '/admin#/site/' . $site->uuid . '/show';
        }

        $this->transactional = true;
    }
}
