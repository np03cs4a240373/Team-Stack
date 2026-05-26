<?php
// ============================================================
// includes/mail.php - Email helper via PHPMailer + Gmail SMTP
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

// Load SMTP credentials from .env
$_mailEnv  = parse_ini_file(__DIR__ . '/../.env');
define('MAIL_FROM',      $_mailEnv['MAIL_FROM']      ?? '');
define('MAIL_FROM_NAME', $_mailEnv['MAIL_FROM_NAME'] ?? 'KaamKhoji');
define('MAIL_SMTP_USER', $_mailEnv['MAIL_SMTP_USER'] ?? '');
define('MAIL_SMTP_PASS', $_mailEnv['MAIL_SMTP_PASS'] ?? '');
unset($_mailEnv);

function sendMail(string $to, string $subject, string $bodyHtml): bool {
    $wrap = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;color:#1e293b;max-width:600px;margin:0 auto;padding:24px;">
        <div style="background:#00b4d8;padding:16px 24px;border-radius:8px 8px 0 0;">
            <h2 style="color:#fff;margin:0;font-size:1.2rem;">KaamKhoji</h2>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;padding:24px;border-radius:0 0 8px 8px;">
            ' . $bodyHtml . '
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">
            <p style="font-size:0.8rem;color:#94a3b8;">KaamKhoji &mdash; Nepal&apos;s Job Portal &middot; <a href="http://kaamkhoji.com" style="color:#00b4d8;">kaamkhoji.com</a></p>
        </div>
    </body></html>';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_SMTP_USER;
        $mail->Password   = MAIL_SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $wrap;
        $mail->AltBody = strip_tags($bodyHtml);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        return false;
    }
}

// Notify seeker that their application was received
function mailApplicationConfirmation(string $seekerEmail, string $seekerName, string $jobTitle, string $company): void {
    $subject = "Application Received – $jobTitle at $company";
    $body = "<h3>Hi $seekerName,</h3>
        <p>Your application for <strong>$jobTitle</strong> at <strong>$company</strong> has been received successfully.</p>
        <p>You can track your application status anytime under <strong>My Applications</strong> on KaamKhoji.</p>
        <p>Best of luck! 🙏</p>";
    sendMail($seekerEmail, $subject, $body);
}

// Notify employer that a new applicant applied
function mailNewApplicantAlert(string $employerEmail, string $employerName, string $jobTitle, string $applicantName): void {
    $subject = "New Application – $jobTitle";
    $body = "<h3>Hi $employerName,</h3>
        <p><strong>$applicantName</strong> has applied for your job posting: <strong>$jobTitle</strong>.</p>
        <p>Log in to your <strong>Employer Dashboard</strong> on KaamKhoji to review the application.</p>";
    sendMail($employerEmail, $subject, $body);
}

// Send password reset link via email
function mailPasswordReset(string $email, string $resetLink): bool {
    $subject = 'Reset Your KaamKhoji Password';
    $body = "<h3>Password Reset Request</h3>
        <p>We received a request to reset the password for your KaamKhoji account associated with this email address.</p>
        <p>Click the button below to reset your password. This link expires in <strong>1 hour</strong>.</p>
        <p style='margin:24px 0;'>
            <a href='" . htmlspecialchars($resetLink) . "'
               style='background:#00b4d8;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:700;display:inline-block;'>
                Reset Password
            </a>
        </p>
        <p style='font-size:0.85rem;color:#64748b;'>If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
        <p style='font-size:0.8rem;color:#94a3b8;'>If the button does not work, copy and paste this URL into your browser:<br>" . htmlspecialchars($resetLink) . "</p>";
    return sendMail($email, $subject, $body);
}

// Notify seeker that their application status changed
function mailStatusChange(string $seekerEmail, string $seekerName, string $jobTitle, string $company, string $newStatus): void {
    $statusMessages = [
        'applied'     => "Your application has been received.",
        'pending'     => "Your application status has been updated to Pending.",
        'reviewed'    => "Your application is being reviewed by the employer.",
        'shortlisted' => "Great news! You have been <strong>shortlisted</strong> for this position. The employer will contact you soon.",
        'interview'   => "You have been invited for an <strong>interview</strong>. Please check your email for further details.",
        'accepted'    => "Congratulations! Your application has been <strong>accepted</strong>. The employer will be in touch with you soon.",
        'rejected'    => "Unfortunately, your application was not selected this time. Don't give up — keep applying!",
        'withdrawn'   => "Your application has been withdrawn.",
    ];
    $message = $statusMessages[$newStatus] ?? "Your application status has been updated to <strong>$newStatus</strong>.";
    $subject = "Application Update – $jobTitle at $company";
    $body = "<h3>Hi $seekerName,</h3>
        <p>There's an update on your application for <strong>$jobTitle</strong> at <strong>$company</strong>:</p>
        <p style='font-size:1.05rem;'>$message</p>
        <p>Log in to <strong>My Applications</strong> on KaamKhoji to view full details.</p>";
    sendMail($seekerEmail, $subject, $body);
}
