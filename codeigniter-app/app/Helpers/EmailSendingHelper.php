<?php

namespace App\Helpers;



class EmailSendingHelper
{
    public function sendVerificationEmail(string $email, string $rawToken): bool
    {
        $emailService = service('email');

        $verificationUrl = site_url("auth/verify-email?token={$rawToken}");

        $emailService->setFrom(
            getenv('email.fromEmail') ?: 'noreply@eastminster.ac.uk',
            getenv('email.fromName') ?: 'Alumni Influencers Platform'
        );
        $emailService->setTo($email);
        $emailService->setSubject('Verify Your Email - Alumni Influencers Platform');

        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #1B2A4A;'>Welcome to Alumni Influencers</h2>
                <p>Thank you for registering. Please verify your email address by clicking the button below.</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$verificationUrl}' 
                       style='background-color: #1B2A4A; color: #ffffff; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; font-size: 16px;'>
                        Verify Email Address
                    </a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$verificationUrl}</p>
                <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                    This link will expire in 24 hours. If you did not register for an account, 
                    please ignore this email.
                </p>
            </div>
        </body>
        </html>";

        $emailService->setMessage($message);
        $emailService->setMailType('html');

        return $emailService->send();
    }

    public function sendPasswordResetEmail(string $email, string $rawToken): bool
    {
        $emailService = service('email');

        $resetUrl = site_url("auth/reset-password?token={$rawToken}");

        $emailService->setFrom(
            getenv('email.fromEmail') ?: 'noreply@eastminster.ac.uk',
            getenv('email.fromName') ?: 'Alumni Influencers Platform'
        );
        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Request - Alumni Influencers Platform');

        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #1B2A4A;'>Password Reset Request</h2>
                <p>We received a request to reset your password. Click the button below to set a new password.</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' 
                       style='background-color: #1B2A4A; color: #ffffff; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; font-size: 16px;'>
                        Reset Password
                    </a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$resetUrl}</p>
                <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                    This link will expire in 1 hour. If you did not request a password reset, 
                    please ignore this email. Your password will remain unchanged.
                </p>
            </div>
        </body>
        </html>";

        $emailService->setMessage($message);
        $emailService->setMailType('html');

        return $emailService->send();
    }


}