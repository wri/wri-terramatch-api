<?php

namespace App\Mail;

use Exception;

class Unmatch extends Mail
{
    public function __construct(String $model, String $firstName = '', String $secondName = '')
    {
        switch ($model) {
            case 'Admin':
                $isAdmin = true;

                break;
            case 'User':
                $isAdmin = false;

                break;
            default:
                throw new Exception();
        }
        if ($isAdmin) {
            $this->subject = 'Unmatch Detected';
            $this->title = 'Unmatch Detected';
            $this->body = e($firstName) . ' and ' . e($secondName) . ' have unmatched.';
        } else {
            $this->subject = 'Someone Has Unmatched With One Of Your Projects';
            $this->title = 'Someone Has Unmatched With One Of Your Projects';
            $this->body = e($firstName) . ' has unmatched with one of your projects.';
        }
    }
}
