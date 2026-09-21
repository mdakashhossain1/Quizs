<!DOCTYPE html>
<html>
<body style="font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8F5FC; padding: 24px; margin: 0;">
    <div style="max-width: 440px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 16px rgba(88, 0, 164, 0.06); text-align: center;">
        <div style="margin-bottom: 20px;">
            <img src="{{ asset('logo.png') }}" alt="Quizs" width="68" height="68" style="border-radius: 16px; display: inline-block;">
        </div>

        <h2 style="color: #1E1E1E; font-size: 22px; font-weight: 700; margin: 0 0 8px;">
            {{ ($isPasswordReset ?? false) ? 'Reset Your Password' : 'Verify Your Email' }}
        </h2>
        
        <p style="color: #757575; font-size: 14px; line-height: 1.5; margin: 0 0 24px;">
            {{ ($isPasswordReset ?? false)
                ? 'Use the 4-digit code below to reset your Quizs password.'
                : 'Use the 4-digit code below to verify your Quizs account.' }}
        </p>

        <div style="background-color: #F1EBF7; border-radius: 12px; padding: 16px 24px; margin: 0 auto 24px; display: inline-block; letter-spacing: 10px; font-size: 32px; font-weight: 800; color: #5800A4; font-family: monospace;">
            {{ $code }}
        </div>

        <p style="color: #8C829B; font-size: 13px; font-weight: 500; margin: 0 0 16px;">
            This code is valid for <strong>{{ $ttlMinutes }} minutes</strong>.
        </p>

        <hr style="border: none; border-top: 1px solid #ECE6F2; margin: 24px 0 16px;">

        <p style="color: #A098AD; font-size: 12px; line-height: 1.4; margin: 0;">
            If you did not request this verification code, you can safely ignore this email.
        </p>
    </div>
</body>
</html>
