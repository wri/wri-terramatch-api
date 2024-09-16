<?php

namespace App\Mail;

class TaskDigestMail extends I18nMail
{

    public function __construct() {
        parent::__construct(null);
        $this->setSubjectKey('task-digest.subject')
                ->setBodyParams([
                    '{projectName}' => "Project Name",
                    '{date}' => "DD/MM/YYYY",
                ])
                ->setTitleKey('task-digest.title')
                ->setTitleParams([
                    '{date}' => "DD/MM/YYYY",
                ])
                ->setBodyKey('task-digest.body')
                ->setCta('task-digest.cta')
                ->setParams([
                    '{tasks}' => "tasks"
                ]);
            $this->transactional = true;
            $this->link = "/admin/matches";
    }

}