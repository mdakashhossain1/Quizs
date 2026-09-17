<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #F8F5FC; padding: 24px;">
    <div style="max-width: 420px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; text-align: center;">
        <div style="margin-bottom: 16px;">
            <img src="{{ asset('logo_icon.png') }}" alt="Quizs" width="56" height="56" style="border-radius: 12px;">
        </div>

        <h2 style="color: #1E1E1E; margin-bottom: 8px;">Target complete! 🎉</h2>
        <p style="color: #757575; font-size: 14px;">
            Nice work, {{ $user->name }} — you completed all {{ $progress->completed_quizzes }} of today's
            {{ $progress->effective_target }} quizzes.
        </p>

        <div style="font-size: 40px; font-weight: 700; color: #5800A4; margin: 20px 0;">
            100%
        </div>

        <p style="color: #757575; font-size: 13px;">
            Come back tomorrow to keep your streak going.
        </p>
    </div>
</body>
</html>
