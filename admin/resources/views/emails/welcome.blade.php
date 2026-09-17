<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #F8F5FC; padding: 24px;">
    <div style="max-width: 460px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px;">
        <div style="text-align: center; margin-bottom: 16px;">
            <img src="{{ asset('logo.png') }}" alt="Quizs" width="72" height="72" style="border-radius: 16px;">
        </div>

        <h2 style="color: #1E1E1E; margin-bottom: 8px; text-align: center;">Welcome to Quizs, {{ $user->name }}!</h2>
        <p style="color: #757575; font-size: 14px; text-align: center;">
            Your email is verified and your account is ready to go.
        </p>

        <div style="margin: 24px 0; font-size: 14px; color: #1E1E1E;">
            <p style="margin: 0 0 12px;">Here's what you can do next:</p>
            <ul style="padding-left: 20px; color: #757575; margin: 0;">
                <li style="margin-bottom: 8px;">Take your first quiz and start building your score.</li>
                <li style="margin-bottom: 8px;">Hit your daily quiz target to earn XP and level up.</li>
                <li style="margin-bottom: 8px;">Track your accuracy and rank on the leaderboard.</li>
            </ul>
        </div>

        <p style="color: #757575; font-size: 13px; text-align: center;">
            Good luck, and have fun!
        </p>
    </div>
</body>
</html>
