<?php
/**
 * mailer($recipient, $msg, $uname)
 * - Attempts to send email using PHPMailer if available and configured.
 * - If PHPMailer is not available, falls back to inserting a record
 *   into the `notifications` table and returns true so existing flows continue.
 */
function mailer($recipient, $msg, $uname)
{
    // Try to load PHPMailer (if added by the admin)
    if (file_exists(__DIR__.'/../vendor/autoload.php')) {
        require_once __DIR__.'/../vendor/autoload.php';
    }

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // configuration file (optional) - admin can create client/email_config.php
            if (file_exists(__DIR__.'/email_config.php')) include __DIR__.'/email_config.php';
            // default SMTP settings (override in email_config.php)
            if (!empty($smtp_host)) {
                $mail->isSMTP();
                $mail->Host = $smtp_host;
                $mail->SMTPAuth = true;
                $mail->Username = $smtp_user;
                $mail->Password = $smtp_pass;
                $mail->SMTPSecure = isset($smtp_secure) ? $smtp_secure : 'tls';
                $mail->Port = isset($smtp_port) ? $smtp_port : 587;
            }

            $mail->setFrom(isset($from_email) ? $from_email : 'no-reply@localhost', isset($from_name) ? $from_name : 'Leave System');
            $mail->addAddress($recipient);
            $mail->Subject = 'Leave Management Notification';
            $mail->Body = nl2br($msg);
            $mail->isHTML(true);
            $mail->send();
            return true;
        } catch (Exception $e) {
            // fall through to notifications table
        }
    }

    // PHPMailer not available or failed — store as notification in DB (if possible)
    try {
        include __DIR__.'/connect.php';
        $to = $conn->real_escape_string($recipient);
        $message = $conn->real_escape_string($msg);
        $uname_db = $conn->real_escape_string($uname);
        $sql = "INSERT INTO notifications(`recipient`,`message`,`created_by`,`created_at`) VALUES('".$to."','".$message."','".$uname_db."',NOW())";
        @$conn->query($sql);
    } catch (
        Throwable $t) {
        // ignore
    }

    // SMS placeholder: admin can integrate third-party gateway and implement sms_send()
    if(function_exists('sms_send')){
        @sms_send($recipient, strip_tags($msg));
    }

    return true;
}
?>


