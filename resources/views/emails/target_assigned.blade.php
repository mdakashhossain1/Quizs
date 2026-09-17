<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #F8F5FC; padding: 24px;">
    <div style="max-width: 420px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; text-align: center;">
        <div style="margin-bottom: 16px;">
            <img src="{{ asset('logo_icon.png') }}" alt="Quizs" width="56" height="56" style="border-radius: 12px;">
        </div>

        <h2 style="color: #1E1E1E; margin-bottom: 8px;">New daily quiz target</h2>
        <p style="color: #757575; font-size: 14px;">
            Hi {{ $user->name }}, an admin has set your daily quiz target to:
        </p>

        <div style="font-size: 40px; font-weight: 700; color: #5800A4; margin: 20px 0;">
            {{ $target }}
        </div>

        <p style="color: #757575; font-size: 13px;">
            Complete {{ $target }} {{ Str::plural('quiz', $target) }} today to hit your target and earn XP.
        </p>
    </div>
</body>
</html>
