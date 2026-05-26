<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

$env = parse_ini_file(__DIR__ . '/.env');
echo "SMTP User: " . $env['MAIL_SMTP_USER'] . "\n";
echo "SMTP Pass length: " . strlen($env['MAIL_SMTP_PASS']) . "\n";
echo "SMTP Pass value: [" . $env['MAIL_SMTP_PASS'] . "]\n";

$mail = new PHPMailer(true);
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
$mail->Debugoutput = 'echo';

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['MAIL_SMTP_USER'];
    $mail->Password   = $env['MAIL_SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($env['MAIL_SMTP_USER'], 'KaamKhoji');
    $mail->addAddress($env['MAIL_SMTP_USER']); // send to self as test
    $mail->isHTML(false);
    $mail->Subject = 'KaamKhoji Mail Test';
    $mail->Body    = 'If you see this, PHPMailer is working!';

    $mail->send();
    echo "\n\nSUCCESS: Email sent!\n";
} catch (Exception $e) {
    echo "\n\nFAILED: " . $mail->ErrorInfo . "\n";
}
