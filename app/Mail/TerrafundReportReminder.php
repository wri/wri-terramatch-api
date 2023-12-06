<?php

namespace App\Mail;

class TerrafundReportReminder extends Mail
{
    public function __construct(int $id)
    {
        $this->subject = 'Terrafund Report Reminder';
        $this->title = 'YOU HAVE A REPORT DUE!';
        $this->body = 'Your next report is due on July 31. It should reflect any progress made between January 1, 2023 and June 30, 2022.<br><br>' .
            'If you have any questions, feel free to message us at info@terramatch.org.' .
            '<br><br>---<br><br>' .
            'Votre prochain rapport doit être remis le 31 juillet. Il doit refléter tous les progrès réalisés entre le 1er janvier 2023 et le 30 juin 2023. ';
        $this->link = '/terrafund/programmeOverview/' . $id;
        $this->cta = 'View Project';

        $this->transactional = true;
    }
}
