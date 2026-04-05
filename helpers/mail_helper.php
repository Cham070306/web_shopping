<?php

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendResetOTP($toEmail, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '3engant@gmail.com';
        $mail->Password = 'brpo ilye ciso dsnm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('3engant@gmail.com', '3legant Support');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Your Password Reset Code - 3legant';

        $mail->Body = '
        <div style="margin:0; padding:0; background-color:#f3f5f7; font-family:Arial, sans-serif;">
            <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e8ecef;">
                
                <div style="background:#141718; padding:24px; text-align:center;">
                    <h1 style="margin:0; color:#ffffff; font-size:28px; font-weight:700;">3legant</h1>
                </div>

                <div style="padding:40px 32px;">
                    <h2 style="margin:0 0 16px; font-size:28px; color:#141718;">Password Reset Request</h2>
                    
                    <p style="margin:0 0 16px; font-size:16px; line-height:1.7; color:#6c7275;">
                        We received a request to reset your password.
                    </p>

                    <p style="margin:0 0 24px; font-size:16px; line-height:1.7; color:#6c7275;">
                        Please use the verification code below to continue:
                    </p>

                    <div style="text-align:center; margin:32px 0;">
                        <div style="
                            display:inline-block;
                            background:#f3f5f7;
                            border:1px dashed #141718;
                            border-radius:12px;
                            padding:18px 30px;
                            font-size:32px;
                            font-weight:700;
                            letter-spacing:10px;
                            color:#141718;
                        ">
                            '.$otp.'
                        </div>
                    </div>

                    <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#6c7275;">
                        This code will expire in <strong style="color:#141718;">5 minutes</strong>.
                    </p>

                    <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#6c7275;">
                        If you did not request a password reset, you can safely ignore this email.
                    </p>

                    <hr style="border:none; border-top:1px solid #e8ecef; margin:28px 0;">

                    <p style="margin:0; font-size:13px; line-height:1.6; color:#a0a0a0; text-align:center;">
                        This is an automated email from <strong>3legant</strong>. Please do not reply directly to this message.
                    </p>
                </div>
            </div>
        </div>';

        $mail->AltBody = "Your 3legant password reset code is: $otp . This code will expire in 5 minutes.";

        return $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        exit;
    }
}