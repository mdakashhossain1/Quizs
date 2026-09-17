<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #F8F5FC; padding: 24px;">
    <div style="max-width: 460px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px;">
        <div style="text-align: center; margin-bottom: 16px;">
            <img src="{{ asset('logo.png') }}" alt="Quizs" width="72" height="72" style="border-radius: 16px;">
        </div>

        <h2 style="color: #1E1E1E; margin-bottom: 8px;">
            {{ $isReset ? 'Your password has been reset' : 'Welcome to Quizs' }}
        </h2>
        <p style="color: #757575; font-size: 14px;">
            Hi {{ $user->name }},
            @if($isReset)
                an administrator has reset the password for your Quizs account. Use the temporary password below to sign in.
            @else
                an administrator has created a Quizs account for you. Use the details below to sign in for the first time.
            @endif
        </p>

        <table style="width: 100%; margin: 20px 0; font-size: 14px; color: #1E1E1E;">
            <tr>
                <td style="padding: 6px 0; color: #757575;">Email</td>
                <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $user->email }}</td>
            </tr>
            @if($user->login_id)
                <tr>
                    <td style="padding: 6px 0; color: #757575;">Login ID</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $user->login_id }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #757575;">Temporary password</td>
                <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #5800A4;">{{ $temporaryPassword }}</td>
            </tr>
        </table>

        <p style="color: #757575; font-size: 13px;">
            You'll be asked to set your own password the first time you sign in with this temporary one.
        </p>
    </div>
</body>
</html>
