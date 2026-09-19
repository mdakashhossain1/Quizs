@extends('legal.layout')

@section('title', 'Privacy Policy')

@section('toc')
    <li><a href="#sec1">1. Introduction &amp; Overview</a></li>
    <li><a href="#sec2">2. Data Controller &amp; Contact</a></li>
    <li><a href="#sec3">3. Categories of Data Collected</a></li>
    <li><a href="#sec4">4. How Data is Collected</a></li>
    <li><a href="#sec5">5. Legal Bases for Processing</a></li>
    <li><a href="#sec6">6. Purposes of Data Processing</a></li>
    <li><a href="#sec7">7. Ad-Free Policy</a></li>
    <li><a href="#sec8">8. Third-Party Subprocessors</a></li>
    <li><a href="#sec9">9. Push Notifications &amp; Alerts</a></li>
    <li><a href="#sec10">10. Device Permissions Explained</a></li>
    <li><a href="#sec11">11. Data Storage &amp; Security</a></li>
    <li><a href="#sec12">12. Data Retention Schedules</a></li>
    <li><a href="#sec13">13. In-App Account Deletion</a></li>
    <li><a href="#sec14">14. Your Global Privacy Rights</a></li>
    <li><a href="#sec15">15. Children's Privacy (COPPA)</a></li>
    <li><a href="#sec16">16. International Data Transfers</a></li>
    <li><a href="#sec17">17. Cookies &amp; Local Storage</a></li>
    <li><a href="#sec18">18. Anti-Cheat &amp; Fair Play Telemetry</a></li>
    <li><a href="#sec19">19. Policy Amendments &amp; History</a></li>
    <li><a href="#sec20">20. Redressal &amp; Contact</a></li>
@endsection

@section('content')
<div class="callout-box info">
    <strong>Executive Summary</strong>
    Quizs is an educational, bilingual (English and Hindi) trivia and quiz mobile application developed by Arknox ("we", "our", or "us"). We respect your fundamental privacy rights and operate our platform according to strict principles of data minimization, transparent processing, purpose limitation, and user sovereignty. This Privacy Policy sets forth our complete practices regarding the collection, transmission, storage, processing, and deletion of user information across our Android, iOS, and backend systems.
</div>

<section id="sec1">
    <h2><span class="sec-num">01.</span> Introduction, Scope &amp; Core Principles</h2>
    <p>
        This Privacy Policy constitutes a legally binding document that governs your access to and interaction with the <strong>Quizs</strong> mobile application (available on Google Play Store and Apple App Store under package identifier <code>com.quizs.application.arknox</code>), its associated backend application programming interfaces (APIs), web endpoints hosted at <code>https://quizs.in</code>, and any associated customer support and notification channels (collectively designated as the "Service").
    </p>
    <p>
        By downloading, installing, launching, creating an account on, or otherwise utilizing Quizs, you acknowledge that you have read, understood, and consented to the collection, disclosure, storage, and processing practices detailed within this comprehensive policy. If you do not agree with any provision contained herein, you must refrain from downloading the application, terminate existing sessions, and delete any associated user accounts immediately.
    </p>
    <p>
        Our operations are firmly rooted in modern international data protection standards, including the <strong>General Data Protection Regulation (EU &amp; UK GDPR)</strong>, the <strong>California Consumer Privacy Act as amended by the California Privacy Rights Act (CCPA/CPRA)</strong>, the <strong>Children's Online Privacy Protection Act (COPPA)</strong>, the <strong>Information Technology Act, 2000 and Digital Personal Data Protection Act, 2023 (India)</strong>, and the strict developer guidelines promulgated by the <strong>Google Play Developer Distribution Agreement</strong> and the <strong>Apple App Store Review Guidelines</strong>.
    </p>
    <p>
        We adhere strictly to four foundational privacy tenets:
    </p>
    <ul>
        <li><strong>Data Minimization:</strong> We only collect information strictly required to authenticate your identity, calculate your educational test scores, preserve your learning streaks, present relevant ads that sustain free access, and maintain application stability.</li>
        <li><strong>No Selling of Personal Data:</strong> We never monetize, sell, trade, broker, or lease your private personal identifiable information (PII) to data aggregators or third-party marketing firms for direct cash remuneration.</li>
        <li><strong>Explicit User Control:</strong> You maintain continuous, uninhibited authority to inspect, update, download, or permanently eradicate your personal profile and gameplay records directly through in-app self-service mechanisms.</li>
        <li><strong>Security by Design:</strong> Every communication channel utilizes modern TLS 1.3 cryptographic protocols, sensitive authentication secrets are stored within hardware-backed device keystores, and server passwords undergo non-reversible salt-hashed cryptographic stretching.</li>
    </ul>
</section>

<section id="sec2">
    <h2><span class="sec-num">02.</span> Data Controller &amp; Contact Information</h2>
    <p>
        For the purposes of applicable data protection legislation (including Art. 4(7) of the GDPR and equivalent definitions under global statutes), the designated Data Controller responsible for the stewardship of your personal data collected via the Quizs platform is:
    </p>
    <div class="callout-box">
        <strong>Data Controller Identification:</strong>
        Organization: <strong>Quizs Application Development Team (Arknox)</strong><br>
        Web Domain: <strong>https://quizs.in</strong><br>
        Primary Contact &amp; Privacy Officer Email: <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a><br>
        Grievance Officer: Designated Data Protection &amp; Legal Compliance Desk, Quizs Portal<br>
        Operating Jurisdiction: India (Serving Global Users in English and Hindi)
    </div>
    <p>
        Any questions, formal inquiries, notices of data subject requests, or regulatory communications regarding our data stewardship should be directed via registered electronic transmission to <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a>. We strive to acknowledge all legitimate inquiries within twenty-four (24) to forty-eight (48) business hours and provide substantive responses within statutory time limits (no later than 30 calendar days).
    </p>
</section>

<section id="sec3">
    <h2><span class="sec-num">03.</span> Categories of Personal Data We Collect</h2>
    <p>
        Depending on whether you navigate the Quizs application as an authenticated account holder, a guest explorer, or an active competitive quiz participant, we may collect and process specific categories of data outlined in the detailed taxonomy below:
    </p>

    <h3>3.1 Information You Explicitly Provide to Us</h3>
    <ul>
        <li>
            <strong>Registration &amp; Profile Identifiers:</strong> When creating a direct user account, you provide your legal or chosen display name, a valid electronic mail address (used for account verification, password recovery, and transactional notifications), and an encrypted authentication password. If you choose to personalize your avatar, you may upload an image via your camera or local photo library.
        </li>
        <li>
            <strong>Google OAuth Identity Tokens:</strong> If you elect to authenticate using "Continue with Google", our application communicates via the official Google Sign-In SDK. Google transmits a cryptographically signed OAuth2 identity token containing your verified primary email address, public display name, unique Google Subject ID, and public profile avatar URL. We do not receive, store, or have access to your Google account password.
        </li>
        <li>
            <strong>Preferences &amp; Customization Settings:</strong> Your preferred instructional language (English or Hindi), in-game sound effect preferences (audio toggles), and notification preference flags stored within the app's secure preferences storage.
        </li>
        <li>
            <strong>Inquiries, Feedback &amp; Support Content:</strong> Any voluntary communications you transmit to our support desk, including bug descriptions, screenshots, feature suggestions, or dispute claims regarding quiz answer keys.
        </li>
    </ul>

    <h3>3.2 Information Generated Through Quiz Play &amp; Gamification</h3>
    <p>
        Quizs is an active educational gaming environment. In order to provide competitive leaderboards, dynamic question algorithms, attendance rewards, and performance metrics, our servers record and evaluate:
    </p>
    <ul>
        <li>
            <strong>Quiz Session Metrics:</strong> The specific category identifier (e.g., Mathematics, Science &amp; Nature, World History, General Knowledge), subcategory or topic selected, quiz initiation timestamp, conclusion timestamp, completion duration per question, chosen option indices, and correctness evaluations.
        </li>
        <li>
            <strong>Scoring, XP &amp; Level Progression:</strong> Accumulated experience points (XP), numeric user level (calculated via cumulative achievement curves), accuracy percentages (ratio of correct responses across all lifetime or periodic attempts), and earned in-game virtual coins.
        </li>
        <li>
            <strong>Attendance &amp; Streak Records:</strong> Consecutive daily login check-in dates, streak continuity records, missed days, bonus coin multiplier milestones, and calendar history visible on the Attendance Screen.
        </li>
        <li>
            <strong>Public Leaderboard Rankings:</strong> Your display name, avatar, level, accuracy rate, and total points as compiled and publicly displayed on the Daily, Weekly, and All-Time global and regional leaderboards.
        </li>
    </ul>

    <h3>3.3 Automatically Collected Device, Diagnostic &amp; Network Telemetry</h3>
    <p>
        When your device interacts with our APIs or loads application views, our automated telemetry capture mechanisms and integrated software development kits (SDKs) process:
    </p>
    <ul>
        <li>
            <strong>Hardware &amp; Platform Telemetry:</strong> Device manufacturer (e.g., Samsung, Xiaomi, Apple, Google), device model designation, CPU architecture, screen dimensions, pixel density, operating system name and version (e.g., Android 14, iOS 17), system language locale, and time zone setting.
        </li>
        <li>
            <strong>Network &amp; Connectivity Data:</strong> Internet Protocol (IP) address utilized upon connection (used transiently for geolocation country determination and DDoS security mitigation), internet service provider (ISP), connection medium (Wi-Fi, Cellular LTE, 5G), and HTTP request headers.
        </li>
        <li>
            <strong>Advertising &amp; Vendor Identifiers:</strong> The Google Advertising ID (GAID on Android devices) or Identifier for Advertisers (IDFA on iOS devices, collected solely where permitted by Apple App Tracking Transparency framework). These pseudonymous identifiers facilitate the delivery, frequency capping, and reporting of in-app banner, interstitial, and rewarded advertisements.
        </li>
        <li>
            <strong>Crash &amp; Exception Telemetry (Firebase Crashlytics):</strong> In the event of an unhandled runtime exception or application crash, Crashlytics records stack traces, memory usage metrics at the moment of failure, storage availability, orientation state, and application build number to enable our engineering team to rapidly isolate and patch defects.
        </li>
        <li>
            <strong>Push Notification Tokens (FCM):</strong> A unique Firebase Cloud Messaging registration token generated by Google services on your device, used exclusively to address push alerts to your individual handset.
        </li>
    </ul>
</section>

<section id="sec4">
    <h2><span class="sec-num">04.</span> Methods &amp; Technologies of Data Collection</h2>
    <p>
        We employ industry-standard, secure methodologies to capture and synchronize data across your mobile hardware and our cloud servers:
    </p>
    <ul>
        <li><strong>Direct API Transmission:</strong> When you tap to answer a quiz question, update your profile, or verify your email, structured JSON payloads are securely transmitted across TLS 1.3 encrypted HTTPS tunnels to our Laravel cloud endpoints.</li>
        <li><strong>Secure Device Storage:</strong> Session authentication tokens (Bearer JWTs) and user cryptographic secrets are stored client-side utilizing <code>FlutterSecureStorage</code>, which binds secrets to the Android Keystore or iOS Keychain hardware security module (HSM). Non-sensitive user preferences (e.g., audio toggle state, cached language) reside in sandboxed <code>SharedPreferences</code>.</li>
        <li><strong>Third-Party Mobile SDKs:</strong> Google Play Services, Firebase SDKs, and Google Mobile Ads SDK run embedded within the application client to safely collect hardware telemetry, deliver localized banner slots, and manage push notifications.</li>
    </ul>
</section>

<section id="sec5">
    <h2><span class="sec-num">05.</span> Legal Bases for Processing (GDPR &amp; Global Standards)</h2>
    <p>
        Under European, British, and corresponding global data protection jurisdictions, data controllers must establish an express lawful basis for each processing activity. We process your personal data under the following legitimate legal frameworks:
    </p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Processing Purpose</th>
                    <th>Categories of Data</th>
                    <th>Legal Basis (GDPR Art. 6)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Account creation, authentication, profile management, and session maintenance</td>
                    <td>Name, email address, password hash, OAuth tokens</td>
                    <td><strong>Performance of a Contract</strong> (Art. 6(1)(b)) — Necessary to provide the requested service and fulfill terms of service.</td>
                </tr>
                <tr>
                    <td>Quiz delivery, scoring, accuracy calculation, level progression, and attendance streak management</td>
                    <td>Quiz responses, question timestamps, accuracy percentage, streak count</td>
                    <td><strong>Performance of a Contract</strong> (Art. 6(1)(b)) — Essential to deliver core gamified educational functionality.</td>
                </tr>
                <tr>
                    <td>Displaying competitive scores on public and regional leaderboards</td>
                    <td>Display name, avatar image, XP, level, accuracy</td>
                    <td><strong>Legitimate Interests</strong> (Art. 6(1)(f)) — Fostering engaging educational competition; balanced with user alias option.</td>
                </tr>
                <tr>
                    <td>Sending transactional notifications, daily streak reminders, and service alerts</td>
                    <td>FCM device token, notification preference settings</td>
                    <td><strong>Consent</strong> (Art. 6(1)(a)) and <strong>Legitimate Interests</strong> (Art. 6(1)(f)) — Controllable via in-app toggle.</td>
                </tr>
                <tr>
                    <td>Displaying non-personalized and contextual in-app advertisements</td>
                    <td>Device model, coarse location (country), app interaction state</td>
                    <td><strong>Legitimate Interests</strong> (Art. 6(1)(f)) — Sustaining the commercial viability of a free educational app.</td>
                </tr>
                <tr>
                    <td>Displaying personalized advertisements tailored to user interests</td>
                    <td>Google Advertising ID (GAID/IDFA), advertising profile telemetry</td>
                    <td><strong>Explicit Consent</strong> (Art. 6(1)(a)) — Collected via the Google User Messaging Platform (UMP) Consent dialog.</td>
                </tr>
                <tr>
                    <td>Crash diagnosis, vulnerability detection, and platform stability improvements</td>
                    <td>Crashlytics logs, device hardware specs, error stack traces</td>
                    <td><strong>Legitimate Interests</strong> (Art. 6(1)(f)) — Maintaining reliable, bug-free software for all users.</td>
                </tr>
                <tr>
                    <td>Preventing cheating, botting, API abuse, and leaderboard manipulation</td>
                    <td>IP address, request rate, completion timestamps, answer timing anomalies</td>
                    <td><strong>Legitimate Interests</strong> (Art. 6(1)(f)) &amp; <strong>Legal Obligation</strong> (Art. 6(1)(c)) — Ensuring platform integrity and fair play.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section id="sec6">
    <h2><span class="sec-num">06.</span> Detailed Purposes of Data Processing</h2>
    <p>
        We process the personal information we accumulate exclusively for the legitimate, transparent operational objectives set forth below:
    </p>
    <ul>
        <li><strong>Provision of Interactive Quizzes:</strong> Dynamically retrieving category-specific questions in your chosen language (English or Hindi), evaluating submitted options against authorized answer keys, calculating real-time score additions, and delivering detailed explanations to augment your educational learning journey.</li>
        <li><strong>Progression &amp; Gamification Architecture:</strong> Calculating accurate user Level milestones, updating lifetime and category-specific accuracy percentages, tracking consecutive attendance check-in streaks, awarding bonus virtual coins, and maintaining persistent user statistics across sessions and device upgrades.</li>
        <li><strong>Community &amp; Social Motivation:</strong> Publishing real-time rank positions on the Daily, Weekly, and All-Time Leaderboard rosters. If you prefer anonymity, you may choose an abstract display pseudonym and default avatar.</li>
        <li><strong>Communications &amp; Service Notices:</strong> Transmitting necessary administrative notices regarding your account security, policy revisions, OTP verification codes for password resets, or responses to customer service inquiries initiated through support channels.</li>
        <li><strong>Push Notifications:</strong> Delivering opt-in reminders to encourage daily learning habits, prevent attendance streak loss, notify you of newly published quiz topics, or highlight periodic competitive tournaments.</li>
        <li><strong>Platform Security &amp; Fair Play Enforcement:</strong> Monitoring API traffic patterns to block distributed denial of service (DDoS) threats, detect automated scripts, prevent decompiled client exploits, and ban accounts attempting to inject fraudulent scores onto leaderboards.</li>
    </ul>
</section>

<section id="sec7">
    <h2><span class="sec-num">07.</span> Ad-Free Policy &amp; No Commercial Tracking</h2>
    <p>
        Quizs is dedicated to delivering an uninterrupted, privacy-respecting educational trivia experience. The Application is operated as a completely ad-free platform.
    </p>
    <p>
        We do not integrate the Google Mobile Ads SDK (AdMob) or any third-party ad network. We do not display banner advertisements, interstitial pop-ups, or rewarded video advertisements. Furthermore, Quizs does not collect, record, or transmit device advertising identifiers (such as Google Advertising ID / GAID on Android or IDFA on iOS) to ad technology vendors, data brokers, or behavioral tracking entities.
    </p>
</section>

<section id="sec8">
    <h2><span class="sec-num">08.</span> Third-Party Service Providers &amp; Subprocessors</h2>
    <p>
        We do not sell your personal data. To deliver a seamless, scalable, and resilient application experience, we entrust specialized third-party cloud infrastructure and software vendors ("Subprocessors") with limited processing duties. All subprocessors are vetted for compliance with global privacy regulations and operate under binding Data Processing Agreements (DPAs):
    </p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Vendor / Service</th>
                    <th>Purpose of Engagement</th>
                    <th>Data Categories Shared</th>
                    <th>Jurisdiction / Safeguards</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Google Firebase Authentication</strong> (Google LLC)</td>
                    <td>Identity management, OAuth 2.0 authentication, secure token issuance</td>
                    <td>Email address, display name, Google Subject ID, session tokens</td>
                    <td>United States / EU Standard Contractual Clauses (SCCs)</td>
                </tr>
                <tr>
                    <td><strong>Google Firebase Cloud Messaging (FCM)</strong></td>
                    <td>Delivering transactional, streak, and quiz reminder push notifications</td>
                    <td>Device push tokens, message payloads, delivery receipts</td>
                    <td>United States / EU SCCs &amp; ISO 27001 Certified</td>
                </tr>
                <tr>
                    <td><strong>Google Firebase Crashlytics</strong></td>
                    <td>Real-time crash reporting, unhandled exception telemetry, stability tracking</td>
                    <td>Stack traces, device hardware specs, OS version, app build number</td>
                    <td>United States / Pseudonymized data, 90-day retention</td>
                </tr>
                <tr>
                    <td><strong>Cloud Hosting &amp; CDN Infrastructure</strong></td>
                    <td>API server hosting, database cluster management, DDoS protection, edge caching</td>
                    <td>Encrypted database entries, IP address, HTTPS request metadata</td>
                    <td>Secure Data Centers (India / Global CDN) / Encrypted at rest (AES-256)</td>
                </tr>
                <tr>
                    <td><strong>Transactional Email Provider</strong></td>
                    <td>Dispatching OTP verification codes, password reset emails, account notices</td>
                    <td>Recipient email address, user display name, timestamp</td>
                    <td>TLS encrypted transmission / Strictly operational, zero marketing lists</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section id="sec9">
    <h2><span class="sec-num">09.</span> Push Notifications &amp; Local Alerts</h2>
    <p>
        Quizs utilizes push notifications via <strong>Firebase Cloud Messaging (FCM)</strong> and scheduled local reminders via <code>flutter_local_notifications</code> to keep you informed and motivated.
    </p>
    <ul>
        <li><strong>Types of Notifications:</strong> Daily study alerts, notices that your consecutive attendance streak is about to expire, notifications when new educational categories or challenge quizzes are published, and milestone alerts when you achieve a new player Level or leaderboard rank.</li>
        <li><strong>User Consent:</strong> In compliance with Android 13+ (API level 33) and iOS requirements, Quizs explicitly requests runtime notification permission (<code>POST_NOTIFICATIONS</code>) before dispatching remote alerts.</li>
        <li><strong>Granular Control:</strong> You can completely disable push notifications at any time directly within the application by navigating to <em>Profile Screen &gt; Notification Toggle</em>, or via your handset's system settings under <em>Settings &gt; Apps &gt; Quizs &gt; Notifications</em>.</li>
    </ul>
</section>

<section id="sec10">
    <h2><span class="sec-num">10.</span> Device Permissions &amp; Hardware Access Explained</h2>
    <p>
        Our mobile application requests only the minimum device permissions strictly required to execute core functionality. We provide complete transparency regarding each permission requested:
    </p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Operating System Permission</th>
                    <th>Classification</th>
                    <th>Justification &amp; Operational Requirement</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>android.permission.INTERNET</code></td>
                    <td>Network Connectivity</td>
                    <td>Required to establish secure HTTPS communication with the Quizs backend API to download questions, upload quiz attempts, synchronize streaks, and authenticate sessions.</td>
                </tr>
                <tr>
                    <td><code>android.permission.ACCESS_NETWORK_STATE</code></td>
                    <td>Network Diagnostics</td>
                    <td>Enables the application to detect whether an active Wi-Fi or cellular data connection exists, preventing crashes and displaying friendly offline error banners.</td>
                </tr>
                <tr>
                    <td><code>android.permission.POST_NOTIFICATIONS</code></td>
                    <td>Alerts (Android 13+)</td>
                    <td>Allows Quizs to post daily challenge reminders and streak freeze alerts. Can be accepted or denied at initial prompt without blocking app usage.</td>
                </tr>
                <tr>
                    <td><code>READ_MEDIA_IMAGES</code> / <code>CAMERA</code></td>
                    <td>Media &amp; Photos</td>
                    <td>Requested exclusively when you choose to customize your profile avatar using the Edit Profile feature via <code>image_picker</code>. We never scan, index, or access any other photos in your media gallery.</td>
                </tr>
                <tr>
                    <td><code>android.permission.VIBRATE</code></td>
                    <td>Haptics</td>
                    <td>Provides subtle tactile haptic feedback during quiz gameplay when tapping answers or when timers alert the user to the final countdown.</td>
                </tr>
                <tr>
                    <td><code>android.permission.WAKE_LOCK</code></td>
                    <td>System Audio</td>
                    <td>Utilized by the internal <code>audioplayers</code> package to ensure smooth, crackle-free playback of short sound effects (button clicks, correct answers, wrong answers).</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section id="sec11">
    <h2><span class="sec-num">11.</span> Data Storage, Architecture &amp; Cryptographic Security</h2>
    <p>
        Protecting your personal data against unauthorized disclosure, interception, destruction, or compromise is a core engineering priority. We maintain industry-standard physical, organizational, and electronic safeguards:
    </p>
    <ul>
        <li><strong>Encryption in Transit:</strong> All data exchanged between your mobile device and our backend servers travels exclusively across encrypted Transport Layer Security (TLS 1.3 and TLS 1.2) tunnels with forward secrecy and strict HTTPS enforcement. Plaintext HTTP traffic is rejected at the edge.</li>
        <li><strong>Client-Side Hardware Keystore:</strong> All sensitive authentication tokens (Bearer JWTs), refresh credentials, and cryptographic keys stored on your mobile hardware are encrypted via <code>FlutterSecureStorage</code>. On Android, keys are generated and shielded within the hardware-backed Android KeyStore provider with AES-256-GCM cipher suites. On iOS, tokens reside in the encrypted iOS Keychain.</li>
        <li><strong>Password Hashing:</strong> User account passwords are never stored in readable plaintext. Passwords undergo irreversible salt-hashed stretching utilizing the modern <strong>bcrypt</strong> algorithm with a high work factor, rendering them immune to rainbow table lookups.</li>
        <li><strong>Database Security at Rest:</strong> Cloud databases containing user profiles and quiz records reside within private virtual cloud networks (VPCs) with strict firewall rules, inaccessible from the public internet, and protected with AES-256 disk encryption at rest.</li>
        <li><strong>Access Controls &amp; Rate Limiting:</strong> All administrative endpoints enforce strict role-based access control (RBAC), multi-factor authentication, and automated rate-limiting algorithms to mitigate brute-force credential stuffing and credential harvesting.</li>
    </ul>

    <h3>11.2 Security Incident Response &amp; Data Breach Notification Protocol</h3>
    <p>
        Quizs maintains a formalized, documented Information Security Incident Response Plan (IRP) designed to identify, contain, remediate, and report potential security compromises involving personal data without undue delay.
    </p>
    <ul>
        <li><strong>Continuous Intrusion Detection:</strong> Our hosting infrastructure employs automated intrusion detection systems (IDS), web application firewalls (WAF), rate-limiting algorithms, and automated anomaly log analysis to identify brute-force credential stuffing, suspicious API payload patterns, or unauthorized database access attempts.</li>
        <li><strong>Containment &amp; Eradication Protocols:</strong> Upon detection of a confirmed or suspected security incident, our security engineering team immediately initiates isolation measures, including revoking compromised API credentials, rotating database encryption keys, severing affected compute instances, and inspecting historical audit trails.</li>
        <li><strong>Regulatory Notification Mandate (72 Hours):</strong> In the unlikely event of a security incident resulting in the accidental or unlawful destruction, loss, alteration, unauthorized disclosure of, or access to personal data, and where such breach presents a risk to the rights and freedoms of natural persons, Quizs will notify competent supervisory authorities (including the relevant European Data Protection Authorities under GDPR Art. 33 and the Indian Computer Emergency Response Team - CERT-In under applicable cyber security directives) within seventy-two (72) hours of becoming aware of the incident.</li>
        <li><strong>Direct User Notification:</strong> If a data breach is determined to present a high risk to the rights and freedoms of affected individuals (such as unauthorized disclosure of login credentials), we will communicate the breach directly to affected account holders via an urgent notice sent to their registered electronic mail addresses and via an prominent in-app security bulletin. Such notification will describe the nature of the breach, the name and contact details of our Data Protection Officer, the likely consequences of the incident, and the specific mitigation measures implemented or recommended to users (such as credential resets).</li>
    </ul>

</section>

<section id="sec12">
    <h2><span class="sec-num">12.</span> Data Retention &amp; Disposal Schedules</h2>
    <p>
        We do not retain personal information longer than is strictly necessary to fulfill the purposes described in this Privacy Policy, satisfy our contractual obligations, or comply with legal mandates:
    </p>
    <ul>
        <li><strong>Active User Accounts:</strong> Your profile data, level progression, badges, virtual coins, accuracy rates, and attendance records remain preserved in our active database for as long as your account remains open and in good standing.</li>
        <li><strong>Quiz Attempt History:</strong> Granular question-by-question attempt logs are retained to compute historical accuracy trends, leaderboard rankings, and anti-cheat validation for up to twenty-four (24) months, after which they are either aggregated into non-identifiable statistical totals or deleted.</li>
        <li><strong>Crash &amp; Diagnostic Reports:</strong> Firebase Crashlytics telemetry data is automatically purged on a rolling ninety (90) day retention schedule per Google's default security standard.</li>
        <li><strong>Server Access Logs:</strong> Web server connection logs (recording IP address, timestamp, requested URI, and user agent string) are preserved for thirty (30) days for intrusion detection and security auditing, then permanently overwritten.</li>
        <li><strong>Inactive Accounts:</strong> If an account demonstrates no login activity or API interactions for a continuous period of twenty-four (24) consecutive months, we reserve the right to archive or purge the inactive profile after transmitting advance notice to the registered email address.</li>
    </ul>
</section>

<section id="sec13">
    <h2><span class="sec-num">13.</span> Account Deletion &amp; Data Erasure (Play Store §13.3 Compliance)</h2>
    <p>
        In accordance with Google Play's User Data Policy regarding Account Deletion and international privacy rights (such as GDPR Art. 17 "Right to Erasure"), Quizs provides seamless, transparent, and direct pathways for users to permanently delete their accounts and all associated data.
    </p>
    <h3>13.1 Instant In-App Deletion</h3>
    <p>
        You do not need to send an email or submit a help ticket to delete your account. You can trigger immediate, permanent deletion directly inside the application at any time:
    </p>
    <ol>
        <li>Open the <strong>Quizs</strong> application and sign in to your account.</li>
        <li>Navigate to the <strong>Profile Screen</strong> (bottom navigation tab).</li>
        <li>Tap the edit pencil icon or open <strong>Edit Profile</strong> (<code>/edit-profile</code>).</li>
        <li>Scroll down and tap the red <strong>Delete Account</strong> button.</li>
        <li>Review the permanent deletion warning modal and confirm your decision.</li>
    </ol>
    <p>
        Once confirmed, our backend executes an automated cascading deletion procedure that permanently eradicates your user row, profile details, password hashes, email address, custom avatars, attendance calendar check-ins, quiz attempt records, notification preferences, and push device tokens from our active database. Client-side authentication tokens stored in your device keystore are cleared immediately, and you are logged out.
    </p>

    <h3>13.2 Web-Based / Email Deletion Requests</h3>
    <p>
        If you have uninstalled the application or cannot access your mobile handset, you can request full account deletion by transmitting an email from your registered email address to <a href="mailto:quizsappliaction@gmail.com?subject=Account%20Deletion%20Request">quizsappliaction@gmail.com</a> with the subject line <em>"Account Deletion Request"</em>. Our security team will verify your ownership of the email account and complete the permanent erasure within seven (7) business days, transmitting confirmation upon completion.
    </p>
</section>

<section id="sec14">
    <h2><span class="sec-num">14.</span> Your Global Privacy Rights (GDPR, CCPA/CPRA, DPDPA)</h2>
    <p>
        Depending on your place of domicile, you are endowed with statutory legal rights regarding the personal information held about you:
    </p>

    <h3>14.1 Rights for European Economic Area (EEA) and United Kingdom Residents (GDPR)</h3>
    <ul>
        <li><strong>Right of Access (Art. 15):</strong> You have the right to obtain confirmation as to whether your personal data is being processed, and request a structured copy of that data.</li>
        <li><strong>Right to Rectification (Art. 16):</strong> You may correct inaccurate or incomplete profile details at any time via <em>Profile &gt; Edit Profile</em>.</li>
        <li><strong>Right to Erasure ("Right to be Forgotten", Art. 17):</strong> You may demand the permanent deletion of your personal records, as detailed in Section 13.</li>
        <li><strong>Right to Restrict Processing (Art. 18):</strong> You have the right to request a temporary pause on the processing of your personal data under specific contested circumstances.</li>
        <li><strong>Right to Data Portability (Art. 20):</strong> You may request an export of your account and quiz statistics in a machine-readable JSON or CSV format.</li>
        <li><strong>Right to Object (Art. 21):</strong> You may object to data processing grounded in our legitimate interests, including the right to opt-out of behavioral profiling and direct marketing.</li>
        <li><strong>Right to Withdraw Consent (Art. 7(3)):</strong> Where processing relies on your consent (e.g., push notifications, personalized ads), you can withdraw consent at any time without penalty.</li>
    </ul>

    <h3>14.2 Rights for California Residents (CCPA / CPRA)</h3>
    <ul>
        <li><strong>Right to Know:</strong> California consumers have the right to request disclosure of the specific categories and pieces of personal information collected, the sources of collection, the commercial purposes of collection, and the categories of third parties with whom data is shared over the preceding 12 months.</li>
        <li><strong>Right to Delete:</strong> You have the right to request the deletion of your personal information, subject to statutory exceptions (such as detecting security incidents or complying with legal obligations).</li>
        <li><strong>Right to Correct:</strong> You have the right to request rectification of inaccurate personal records.</li>
        <li><strong>Right to Opt-Out of Sale or Sharing:</strong> Quizs does not sell personal information for monetary remuneration. However, under the CCPA's broad definition of "sharing" for cross-context behavioral advertising, transferring advertising identifiers (such as GAID/IDFA) to ad networks may constitute "sharing". You can opt out via operating system ad tracking toggles or our in-app consent preferences.</li>
        <li><strong>Right to Non-Discrimination:</strong> We will never deny services, charge different prices, or degrade your gameplay experience because you exercised any of your CCPA rights.</li>
    </ul>

    <h3>14.3 Rights for Indian Residents (Digital Personal Data Protection Act, 2023)</h3>
    <ul>
        <li><strong>Right to Access Summary:</strong> The right to obtain a summary of personal data being processed and the processing activities undertaken.</li>
        <li><strong>Right to Correction &amp; Erasure:</strong> The right to have misleading, inaccurate, or outdated personal data rectified or permanently erased.</li>
        <li><strong>Right to Grievance Redressal:</strong> The right to readily accessible grievance redressal mechanisms through our Grievance Officer at <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a>.</li>
        <li><strong>Right to Nominate:</strong> The right to nominate an individual who, in the event of death or incapacity, shall exercise your privacy rights.</li>
    </ul>

    <h3>14.4 Notice to Residents of Other US States (Virginia, Colorado, Connecticut, Utah, Texas, Oregon, Montana)</h3>
    <p>
        If you reside in Virginia (Virginia Consumer Data Protection Act), Colorado (Colorado Privacy Act), Connecticut (Connecticut Data Privacy Act), Utah (Utah Consumer Privacy Act), Texas (Texas Data Privacy and Security Act), Oregon, Montana, or another US state with enacted comprehensive consumer privacy statutes, you are endowed with rights analogous to those outlined for California residents. Specifically:
    </p>
    <ul>
        <li><strong>Right of Access &amp; Portability:</strong> You have the right to confirm whether Quizs processes your personal data and obtain a copy in a portable, readily usable format.</li>
        <li><strong>Right to Correct Inaccuracies:</strong> You have the right to require correction of inaccuracies in your personal data, taking into account the nature of the data and purposes of processing.</li>
        <li><strong>Right to Delete:</strong> You have the right to delete personal data provided by or obtained about you.</li>
        <li><strong>Right to Opt Out of Targeted Advertising &amp; Profiling:</strong> You have the right to opt out of the processing of your personal data for purposes of targeted advertising, the sale of personal data, or profiling in furtherance of decisions that produce legal or similarly significant effects. Quizs does not engage in profiling producing legal consequences, and targeted advertising can be opted out of via operating system controls.</li>
        <li><strong>Appeals Process:</strong> If Quizs declines to take action regarding your consumer rights request, you have the right to appeal our decision within forty-five (45) calendar days of receiving our denial notice. To submit an appeal, please email <a href="mailto:quizsappliaction@gmail.com?subject=Privacy%20Appeal">quizsappliaction@gmail.com</a> with the subject line <em>"Privacy Rights Appeal"</em>. We will respond within forty-five (45) days (or sixty (60) days where permitted by state statute) explaining our reasoning and providing instructions on how to submit a complaint to your state Attorney General if you remain dissatisfied.</li>
    </ul>

</section>

<section id="sec15">
    <h2><span class="sec-num">15.</span> Children's Privacy (COPPA &amp; Global Standards)</h2>
    <p>
        Quizs provides educational, general audience trivia and quiz content encompassing school science, history, geography, mathematics, and literature. However, the application is strictly designed for individuals aged <strong>13 years or older</strong> (or 16 years old in relevant European jurisdictions where mandated by local member state legislation).
    </p>
    <p>
        We do not knowingly solicit, collect, or process personal identifiable information from children under the age of 13. If you are under 13 years of age, you are prohibited from creating an account or providing your name, email address, or photos to the application.
    </p>
    <p>
        If a parent, legal guardian, or educator discovers that a child under 13 has registered an account with Quizs without parental verification, please notify us immediately at <a href="mailto:quizsappliaction@gmail.com?subject=Child%20Privacy%20Inquiry">quizsappliaction@gmail.com</a>. Upon receipt of verified notice, we will immediately delete the associated account and purge all related personal data from our cloud databases.
    </p>
</section>

<section id="sec16">
    <h2><span class="sec-num">16.</span> International Cross-Border Data Transfers</h2>
    <p>
        Quizs operates globally and serves users across multiple continents. Your personal information may be transferred to, stored at, and processed in cloud servers located outside your country of residence (including India and the United States, where Google and cloud hosting data centers are situated).
    </p>
    <p>
        Whenever we transfer personal data across international borders, we ensure that adequate safeguards are instituted in full compliance with applicable statutory requirements:
    </p>
    <ul>
        <li>Utilizing the European Commission's approved <strong>Standard Contractual Clauses (SCCs)</strong> for data transfers between EU and non-EU entities.</li>
        <li>Relying on adequacy decisions promulgated by relevant regulatory authorities where applicable.</li>
        <li>Enforcing strict technical safeguards, including end-to-end transport layer encryption and AES-256 database storage encryption.</li>
    </ul>
</section>

<section id="sec17">
    <h2><span class="sec-num">17.</span> Cookies, Tracking Technologies &amp; Local Cache</h2>
    <p>
        On our mobile client, we do not utilize traditional web browser cookies. Instead, our app utilizes localized native persistence mechanisms:
    </p>
    <ul>
        <li><strong>Secure Storage (FlutterSecureStorage):</strong> Used exclusively to hold your authenticated session authorization token.</li>
        <li><strong>Shared Preferences (SharedPreferences):</strong> Used to remember your selected language (English or Hindi), sound effects toggle state, and notification preference flags so that your app experience remains personalized upon restart.</li>
        <li><strong>Web Portal Cookies:</strong> If you visit our public web portal at <code>https://quizs.in</code>, minimal session cookies and Cloudflare security tokens may be stored in your browser solely to route traffic and protect the server cluster against malicious web scrapers.</li>
    </ul>
</section>

<section id="sec18">
    <h2><span class="sec-num">18.</span> Anti-Cheat Telemetry &amp; Fair Play Auditing</h2>
    <p>
        To ensure fair educational competition on our global leaderboards and protect our virtual coin economy from malicious manipulation, our backend automated systems monitor game telemetry for anomalies:
    </p>
    <ul>
        <li>Submitting answer responses at physiologically impossible speeds (e.g., answering 20 questions in 200 milliseconds).</li>
        <li>Repeated automated API calls indicative of headless bot scripts or scrapers.</li>
        <li>Attempting to alter client-side scoring logic or intercept and forge HTTP API payloads.</li>
    </ul>
    <p>
        Telemetry collected for anti-cheat verification (such as response duration timestamps and client app build signatures) is strictly utilized for security and integrity verification and is never shared with third-party advertisers. Accounts verified to have utilized cheats, automation bots, or memory injectors are subject to immediate disqualification and permanent termination.
    </p>
</section>

<section id="sec19">
    <h2><span class="sec-num">19.</span> Policy Amendments, Versioning &amp; Notifications</h2>
    <p>
        As the Quizs platform evolves with new features, expanded quiz categories, or altered regulatory frameworks, we may periodically revise this Privacy Policy.
    </p>
    <p>
        When substantive, material changes are introduced to how we collect, store, or share your personal data, we will provide conspicuous notice before the updates take effect. Such notification may be delivered via an in-app banner modal, an alert displayed upon login, or an electronic transmission sent to your registered email address.
    </p>
    <p>
        The date of the most recent revision will always be displayed prominently at the top and bottom of this document. We encourage you to periodically review this page to stay informed of our data protection practices. Continued utilization of the Quizs platform after the effective date of an amended policy constitutes affirmative acceptance of the revised terms.
    </p>
</section>

<section id="sec20">
    <h2><span class="sec-num">20.</span> Redressal, Supervisory Authorities &amp; Contact</h2>
    <p>
        If you have questions, concerns, comments, or grievances regarding this Privacy Policy, our data stewardship, or if you wish to exercise any of your statutory privacy rights, please reach out to our dedicated privacy desk:
    </p>
    <div class="callout-box">
        <strong>Official Privacy &amp; Data Protection Office:</strong><br>
        Application: <strong>Quizs (Bilingual Educational Trivia App)</strong><br>
        Publisher: <strong>Arknox Development Team</strong><br>
        Email: <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a><br>
        Website: <a href="https://quizs.in">https://quizs.in</a><br>
        In-App Route: <em>Profile &gt; Privacy Policy</em> or <em>Profile &gt; Edit Profile &gt; Delete Account</em>
    </div>
    <p>
        If you reside within the European Union, United Kingdom, or another jurisdiction with a designated Data Protection Authority (DPA) and believe that our processing of your personal data infringes applicable data protection statutes, you retain the right to lodge a formal complaint with your local supervisory regulatory authority (such as the Information Commissioner's Office in the UK, the CNIL in France, or the BfDI in Germany). However, we encourage you to contact us directly first so that we may promptly resolve your concerns in good faith.
    </p>
</section>
@endsection
