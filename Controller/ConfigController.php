<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/PHPMailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/PHPMailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/PHPMailer/src/SMTP.php';

function GenerarContrasenna()
{
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $contrasenna = '';

    for ($i = 0; $i < 8; $i++) {
        $contrasenna .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    return $contrasenna;
}

function EnviarCorreo($asunto, $contenido, $destinatario)
{
    // cuenta de Gmail desde la que se envían los correos.
    $correoSalida = '';
    $contrasennaSalida = '';

    // si el correo no está configurado se simula el envío igualmente
    if ($correoSalida == '' || $contrasennaSalida == '') {
        return true;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $correoSalida;
        $mail->Password = $contrasennaSalida;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correoSalida, 'Lifty');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $contenido;

        $mail->send();
        return true;
    }
    catch (Exception $error) {
        return false;
    }
}
