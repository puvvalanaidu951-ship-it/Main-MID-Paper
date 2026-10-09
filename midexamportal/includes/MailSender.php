<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender
{
    protected static ?string $lastError = null;

    public static function send(string $to, string $subject, string $body): bool
    {
        self::$lastError = null;
        $config = require __DIR__ . '/../config/config.php';
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['email']['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['email']['smtp_user'];
            $mail->Password = $config['email']['smtp_pass'];
            $mail->SMTPSecure = $config['email']['smtp_secure'];
            $mail->Port = $config['email']['smtp_port'];

            $mail->setFrom($config['email']['from'], $config['email']['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            self::$lastError = $e->getMessage();
            return false;
        }
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }
}
