@extends('legal.layout')

@section('title', 'Delete Your Account & Data')

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <span class="badge-danger">Google Play User Data Policy Compliance</span>
        <span style="font-size: 13px; color: var(--ink-muted);">Self-Service Account Removal</span>
    </div>

    <h2><span class="sec-num">01.</span> Request Account &amp; Data Deletion</h2>
    
    <p>
        In strict compliance with <strong>Google Play's User Data &amp; Account Deletion Policy</strong> and international privacy regulations (including GDPR Art. 17 <em>"Right to Erasure"</em> and India's DPDPA), Quizs enables any user to permanently delete their account and purge all personal records directly from our servers without needing to keep the mobile application installed.
    </p>

    @if(session('success'))
        <div class="callout-box success">
            <strong>✓ Account Deletion Request Processed</strong>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="callout-box danger">
            <strong>⚠ Deletion Error</strong>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="callout-box danger">
            <strong>⚠ Please correct the following errors:</strong>
            <ul style="margin: 8px 0 0 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="deletion-form-card">
        <h3 style="margin-top: 0; color: #991B1B; display: flex; align-items: center; gap: 8px;">
            <svg style="width: 22px; height: 22px; fill: #DC2626;" viewBox="0 0 24 24">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
            </svg>
            Permanent Account Deletion Form
        </h3>
        <p style="font-size: 14px; color: var(--ink-secondary); margin-bottom: 20px;">
            Please provide the email address registered with your Quizs account. Upon verification of your confirmation below, your profile, authentication tokens, quiz progress, attendance streaks, and leaderboard scores will be permanently wiped from our database.
        </p>

        <form action="{{ url('/delete-account') }}" method="POST" onsubmit="return confirm('WARNING: Are you absolutely sure you want to permanently delete your Quizs account? This action cannot be reversed.');">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">
                    Registered Account Email Address <span class="required">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="e.g., yourname@gmail.com" 
                    value="{{ old('email', $prefilledEmail ?? '') }}" 
                    required 
                    autocomplete="email"
                >
                <div class="form-help">Enter the email you use to sign in with Google or email/password in the Quizs app.</div>
            </div>

            <div class="form-group">
                <label class="form-checkbox-wrapper">
                    <input 
                        type="checkbox" 
                        name="confirmation" 
                        value="1" 
                        class="form-checkbox" 
                        {{ old('confirmation') ? 'checked' : '' }} 
                        required
                    >
                    <span class="form-checkbox-text">
                        <strong>I understand that this action is permanent and irreversible.</strong>
                        I acknowledge that my profile, quiz history, accumulated XP points, coins, streaks, and test answers will be permanently deleted and cannot be recovered.
                    </span>
                </label>
            </div>

            <button type="submit" class="btn-delete-account">
                <svg viewBox="0 0 24 24">
                    <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                </svg>
                Delete My Account &amp; All Associated Data
            </button>
        </form>
    </div>

    <h2><span class="sec-num">02.</span> What Data is Permanently Deleted?</h2>
    <p>
        When you submit an account deletion request through this page or inside the mobile app (<em>Profile &gt; Edit Profile &gt; Delete Account</em>), the following data categories are immediately purged:
    </p>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Data Category</th>
                    <th style="width: 45%;">Items Included</th>
                    <th style="width: 25%;">Deletion Timeline</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>User Credentials &amp; Profile</strong></td>
                    <td>Full Name, Email Address, Login ID, Avatar image reference, Google OAuth identifier.</td>
                    <td><span style="color: #059669; font-weight: 700;">Immediate</span></td>
                </tr>
                <tr>
                    <td><strong>Quiz Gameplay &amp; Scoring</strong></td>
                    <td>Quiz attempts, answers selected, right/wrong answer counts, time spent per quiz.</td>
                    <td><span style="color: #059669; font-weight: 700;">Immediate</span></td>
                </tr>
                <tr>
                    <td><strong>Progress &amp; Gamification</strong></td>
                    <td>Daily streak count, attendance records, level progression, XP points, and target history.</td>
                    <td><span style="color: #059669; font-weight: 700;">Immediate</span></td>
                </tr>
                <tr>
                    <td><strong>Sessions &amp; Push Notifications</strong></td>
                    <td>Sanctum API personal access tokens, Firebase Cloud Messaging (FCM) device tokens, notification inbox logs.</td>
                    <td><span style="color: #059669; font-weight: 700;">Immediate</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="callout-box info">
        <strong>Data Retention Disclosures:</strong>
        Quizs does not sell personal data. Financial transaction logs (if applicable through third-party payment processors) are retained only where mandated by applicable tax and legal obligations. General anonymized crash analytics aggregated by Google Crashlytics contain no personal identifiers.
    </div>

    <h2><span class="sec-num">03.</span> Alternative In-App Deletion Method</h2>
    <p>
        If you currently have the Quizs app open on your mobile device, you can also delete your account directly inside the app at any time:
    </p>
    <ol>
        <li>Open the <strong>Quizs</strong> application on your Android or iOS device.</li>
        <li>Tap on the <strong>Profile</strong> tab in the bottom navigation bar.</li>
        <li>Tap on <strong>Edit Profile</strong>.</li>
        <li>Scroll down to the <em>Danger Zone</em> and tap <strong>Delete Account</strong>.</li>
        <li>Confirm your decision in the popup dialog. Your account will be immediately deleted and you will be signed out.</li>
    </ol>

    <h2><span class="sec-num">04.</span> Questions or Manual Assistance</h2>
    <p>
        If you experience any difficulties deleting your account or require confirmation of complete data purge, please contact our Data Protection Officer directly:
    </p>
    <ul>
        <li><strong>Email:</strong> <a href="mailto:quizsappliaction@gmail.com?subject=Account%20Deletion%20Assistance">quizsappliaction@gmail.com</a></li>
        <li><strong>Response Time:</strong> Within 24–48 business hours</li>
        <li><strong>Publisher:</strong> Arknox Development Team</li>
    </ul>
@endsection
