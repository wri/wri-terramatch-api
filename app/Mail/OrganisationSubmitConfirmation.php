<?php

namespace App\Mail;

class OrganisationSubmitConfirmation extends Mail
{
    public function __construct()
    {
        $this->subject = 'Organization request submitted to TerraMatch.';
        $this->title = 'ORGANIZATION REQUEST SUBMITTED TO TERRAMATCH.';
        $this->body = 'Your organization has been submitted and is in review with WRI. You can continue to use the platform to whilst your ' .
            'application is in review. If you have any questions, feel free to message us at info@terramatch.org.<br><br>' .
            '<br><br>--<br>' .
            'Votre organisation a été soumise et est en cours d\'examen par le WRI. Vous pouvez continuer à utiliser la plateforme pendant que ' .
            'votre demande est en cours d\'examen. Si vous avez des questions, n\'hésitez pas à nous envoyer un message à info@terramatch.org.';
    }
}
