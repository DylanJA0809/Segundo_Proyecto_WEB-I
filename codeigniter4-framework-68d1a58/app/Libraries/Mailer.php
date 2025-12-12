<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function __construct()
    {
        // Cargar los archivos de PHPMailer manualmente
        require_once APPPATH . 'ThirdParty/PHPMailer/src/Exception.php';
        require_once APPPATH . 'ThirdParty/PHPMailer/src/PHPMailer.php';
        require_once APPPATH . 'ThirdParty/PHPMailer/src/SMTP.php';
    }

    /**
     * Envía un correo utilizando PHPMailer con Gmail.
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $html
     * @param string $text
     * @return bool
     */
    public function send(string $toEmail, string $toName, string $subject, string $html, string $text): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Config SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'aaventoneslocal@gmail.com';
            $mail->Password   = 'neub wuos hvtn ffsf'; // contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remitente y destinatario
            $mail->setFrom('aaventoneslocal@gmail.com', 'Aventones Local');
            $mail->addAddress($toEmail, $toName);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;

                $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages
                $mail->Debugoutput = function ($str, $level) {
                    echo "SMTP[$level]: $str\n";
                };

            $mail->send();
            return true;
        } catch (Exception $e) {
            //log_message('error', 'Error al enviar correo: ' . $mail->ErrorInfo);
            //return false;
            echo "EXCEPTION: " . $e->getMessage() . PHP_EOL;
            echo "ERRORINFO: " . $mail->ErrorInfo . PHP_EOL;
            log_message('error', 'Mailer Exception: ' . $e->getMessage());
            log_message('error', 'Mailer ErrorInfo: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
