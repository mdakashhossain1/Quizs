@extends('legal.layout')

@section('title', 'Privacy Policy')

@section('content')
<p>Quizs ("we", "us") is a bilingual (English/Hindi) quiz app. This policy explains what data we collect and how we use it.</p>

<section>
    <h2>Information we collect</h2>
    <ul>
        <li>Account details you provide: name, email address, and an optional profile photo.</li>
        <li>Gameplay data: quizzes attempted, answers, scores, streaks, and leaderboard rankings.</li>
        <li>Device information: a device identifier and push-notification token, used to keep you signed in and to deliver notifications.</li>
        <li>Diagnostic data: crash and performance reports (via Firebase Crashlytics) to help us fix bugs.</li>
    </ul>
</section>

<section>
    <h2>How we use your information</h2>
    <ul>
        <li>To operate core features: quiz play, scoring, streaks, and leaderboards.</li>
        <li>To send you notifications about your activity, streaks, and app updates (you can turn these off in Profile &gt; Notification).</li>
        <li>To show ads that help keep Quizs free, served through Google Mobile Ads. These may use advertising identifiers as governed by Google's own policies.</li>
        <li>To diagnose crashes and improve app stability and performance.</li>
    </ul>
</section>

<section>
    <h2>Data sharing</h2>
    <p>We do not sell your personal data. We share data only with the service providers that run the app on our behalf (e.g. Firebase for authentication, notifications, and crash reporting; Google Mobile Ads for advertising), and only as needed for them to provide that service.</p>
</section>

<section>
    <h2>Data retention and control</h2>
    <p>Your account data is retained while your account is active. You can update your profile at any time from Profile &gt; Edit Profile, and you can request account deletion by contacting us.</p>
</section>

<section>
    <h2>Children's privacy</h2>
    <p>Quizs is intended for a general audience and does not knowingly collect personal data from children under 13 beyond what is needed for basic gameplay.</p>
</section>

<section>
    <h2>Contact</h2>
    <p>Questions about this policy can be sent to <a href="mailto:support@quizs.in">support@quizs.in</a>.</p>
</section>
@endsection
