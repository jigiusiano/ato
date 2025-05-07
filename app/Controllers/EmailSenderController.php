<?php

namespace App\Controllers;

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSenderController
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('PHPMAILER_SMTPHOST');
            $this->mailer->SMTPAuth = getenv('PHPMAILER_SMTPAUTH');
            $this->mailer->Username = getenv('PHPMAILER_SMTPUSER');
            $this->mailer->Password = getenv('PHPMAILER_SMTPPASS');
            $this->mailer->SMTPSecure = getenv('PHPMAILER_SMTPSECURE');
            $this->mailer->Port = getenv('PHPMAILER_SMTPPORT');

            $this->mailer->setFrom(getenv('PHPMAILER_SMTPUSER'), 'ATO');
        } catch (Exception $e) {
            throw new Exception("Error al configurar PHPMailer: {$this->mailer->ErrorInfo}");
        }
    }

    public function sendEmail(
        string $to,
        string $subject,
        string $body,
    ): bool {
        try {
            $this->mailer->clearAddresses();

            $this->mailer->addAddress($to);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
