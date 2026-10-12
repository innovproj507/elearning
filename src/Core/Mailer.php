<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    public static function send(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        $mail = new PHPMailer(true);

        try {
            if (($_ENV['MAIL_DRIVER'] ?? 'smtp') === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = $_ENV['MAIL_HOST']       ?? 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['MAIL_USERNAME']   ?? '';
                $mail->Password   = $_ENV['MAIL_PASSWORD']   ?? '';
                $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
            } else {
                $mail->isMail();
            }

            $mail->CharSet  = 'UTF-8';
            $mail->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@pamel.edu.pa',
                $_ENV['MAIL_FROM_NAME']    ?? 'PAMEL LMS'
            );
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $htmlBody));

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function resetPasswordEmail(string $to, string $name, string $code): string
    {
        $appName = $_ENV['APP_NAME'] ?? 'PAMEL LMS';
        $appUrl  = $_ENV['APP_URL']  ?? '';

        return "<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width'></head>
<body style='margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f3f4f6;padding:40px 0;'>
    <tr><td align='center'>
      <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <!-- Header -->
        <tr><td style='background:#1e3a5f;padding:32px;text-align:center;'>
          <span style='color:#ffffff;font-size:22px;font-weight:900;letter-spacing:-0.5px;'>{$appName}</span><br>
          <span style='color:#93c5fd;font-size:13px;'>Maritime Training</span>
        </td></tr>
        <!-- Body -->
        <tr><td style='padding:40px 40px 24px;'>
          <p style='color:#374151;font-size:15px;margin:0 0 8px;'>Hola, <strong>{$name}</strong></p>
          <p style='color:#6b7280;font-size:14px;margin:0 0 28px;line-height:1.6;'>
            Recibimos una solicitud para restablecer la contraseña de tu cuenta.<br>
            Usa el siguiente código de verificación:
          </p>
          <!-- Code box -->
          <div style='background:#eff6ff;border:2px dashed #3b82f6;border-radius:12px;padding:24px;text-align:center;margin:0 0 28px;'>
            <span style='font-size:40px;font-weight:900;letter-spacing:12px;color:#1e3a5f;font-family:monospace;'>{$code}</span>
          </div>
          <p style='color:#6b7280;font-size:13px;margin:0 0 8px;'>
            ⏱ Este código expira en <strong>30 minutos</strong>.
          </p>
          <p style='color:#6b7280;font-size:13px;margin:0 0 28px;'>
            Si no solicitaste este cambio, ignora este mensaje. Tu contraseña no será modificada.
          </p>
          <hr style='border:none;border-top:1px solid #e5e7eb;margin:0 0 24px;'>
          <p style='color:#9ca3af;font-size:12px;margin:0;text-align:center;'>
            {$appName} &bull; {$appUrl}
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";
    }
}
