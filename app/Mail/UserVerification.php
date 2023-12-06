<?php

namespace App\Mail;

class UserVerification extends Mail
{
    public function __construct(String $token, string $callbackUrl = null)
    {
        $this->subject = 'Verify Your Email Address';
        $this->title = 'VERIFY YOUR EMAIL ADDRESS';
        $this->body = 'Follow the below link to verify your email address. It\'s valid for 48 hours.  If the link does not work, log on ' .
            'to TerraMatch and resubmit a verfication request. <br>' .
            'If you continue to have problems accessing your account, feel free to message us at info@terramatch.org.' .
            '<br><br>-----<br><br>' .
            'Suivez le lien ci-dessous pour vérifier votre adresse e-mail. Ce lien est valable pendant 48 heures.  Si le lien ne fonctionne pas, ' .
            'connectez-vous à TerraMatch et soumettez à nouveau une demande de vérification.<br>' .
            'Si vous continuez à avoir des problèmes pour accéder à votre compte, n\'hésitez pas à nous envoyer un message à l\'adresse info@terramatch.org.';
        $this->link = $callbackUrl ?
            $callbackUrl . urlencode($token) :
            '/verify?token=' . urlencode($token);
        $this->cta = 'VERIFY EMAIL ADDRESS';
        $this->transactional = true;
    }
}
