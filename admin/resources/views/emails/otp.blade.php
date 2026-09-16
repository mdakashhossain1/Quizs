<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #F8F5FC; padding: 24px;">
    <div style="max-width: 420px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; text-align: center;">
        <h2 style="color: #1E1E1E; margin-bottom: 8px;">Verify your email</h2>
        <p style="color: #757575; font-size: 14px;">Use the code below to verify your Quizs account.</p>
        <div style="font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #5800A4; margin: 24px 0;">
            {{ $code }}
        </div>
        <p style="color: #757575; font-size: 13px;">This code expires in {{ $ttlMinutes }} minutes.</p>
    </div>
</body>
</html>
