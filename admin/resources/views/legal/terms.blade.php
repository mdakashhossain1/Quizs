@extends('legal.layout')

@section('title', 'Terms & Conditions')

@section('toc')
    <li><a href="#sec1">1. Acceptance of Terms</a></li>
    <li><a href="#sec2">2. Definitions &amp; Interpretation</a></li>
    <li><a href="#sec3">3. Eligibility &amp; User Age</a></li>
    <li><a href="#sec4">4. Account Registration &amp; Security</a></li>
    <li><a href="#sec5">5. Limited License &amp; IP Rights</a></li>
    <li><a href="#sec6">6. Educational Disclaimer &amp; Trivia</a></li>
    <li><a href="#sec7">7. Fair Play &amp; Anti-Cheating</a></li>
    <li><a href="#sec8">8. Virtual Economy: Coins &amp; XP</a></li>
    <li><a href="#sec9">9. Attendance &amp; Daily Streaks</a></li>
    <li><a href="#sec10">10. Leaderboards &amp; Rankings</a></li>
    <li><a href="#sec11">11. In-App Advertisements (AdMob)</a></li>
    <li><a href="#sec12">12. User Conduct &amp; Prohibited Acts</a></li>
    <li><a href="#sec13">13. User Avatars &amp; Uploaded Media</a></li>
    <li><a href="#sec14">14. Service Availability &amp; Updates</a></li>
    <li><a href="#sec15">15. Termination &amp; Account Deletion</a></li>
    <li><a href="#sec16">16. Disclaimer of Warranties</a></li>
    <li><a href="#sec17">17. Limitation of Liability</a></li>
    <li><a href="#sec18">18. Indemnification Obligations</a></li>
    <li><a href="#sec19">19. Governing Law &amp; Arbitration</a></li>
    <li><a href="#sec20">20. Miscellaneous Provisions</a></li>
    <li><a href="#sec21">21. Contact &amp; Legal Notices</a></li>
@endsection

@section('content')
<div class="callout-box info">
    <strong>Binding Legal Agreement</strong>
    These Terms and Conditions constitute an enforceable legal agreement between you ("User", "you", or "your") and Arknox, publisher of the Quizs bilingual educational mobile application and platform ("Quizs", "Company", "we", "us", or "our"). By downloading, installing, accessing, creating an account on, or playing Quizs, you agree to be bound by every term, condition, guideline, and policy set forth herein. If you do not accept these terms in their entirety, you must discontinue use and uninstall the application immediately.
</div>

<section id="sec1">
    <h2><span class="sec-num">01.</span> Acceptance of Terms &amp; Scope of Agreement</h2>
    <p>
        Please review these Terms and Conditions carefully before installing, registering for, or using the <strong>Quizs</strong> mobile application (available on Google Play Store and Apple App Store under package identifier <code>com.quizs.application.arknox</code>), its associated APIs, web portals located at <code>https://quizs.in</code>, and related educational gaming services (collectively referred to as the "Service" or "Application").
    </p>
    <p>
        These Terms govern your legal relationship with Quizs and delineate the terms under which you may access our educational quiz categories, compete on public leaderboards, accumulate virtual coins and experience points, participate in daily attendance streaks, and interact with fellow learners. By clicking "Sign In", "Sign Up", "Continue with Google", or otherwise accessing the Service, you signify your affirmative, legally binding assent to these Terms, our <a href="https://quizs.in/privacy">Privacy Policy</a>, and any supplemental guidelines posted within the Application.
    </p>
    <p>
        If you are entering into this agreement on behalf of a minor child, you represent and warrant that you are the lawful parent or legal guardian of such minor and accept full civil and legal responsibility for their compliance with these Terms.
    </p>
</section>

<section id="sec2">
    <h2><span class="sec-num">02.</span> Definitions &amp; Statutory Interpretations</h2>
    <p>
        Throughout these Terms and Conditions, the following capitalized terms shall have the respective meanings ascribed to them:
    </p>
    <ul>
        <li><strong>"Account"</strong> means the unique, authenticated user profile created by or assigned to an individual user via direct email registration or Google OAuth authentication.</li>
        <li><strong>"App" or "Application"</strong> refers to the compiled mobile software binaries titled "Quizs", published by Arknox for Android and iOS devices, including all updates, patches, assets, sound effects, and underlying source code.</li>
        <li><strong>"Content"</strong> encompasses all quiz questions, answer options, educational hints, post-quiz explanations, category names, descriptions, graphics, animations, typography, illustrations, UI components, sound effects, and software algorithms embodied in the Service.</li>
        <li><strong>"Virtual Items"</strong> denotes non-monetary digital elements within the game economy, including Virtual Coins, Experience Points (XP), Player Levels, Achievement Badges, and Attendance Streaks.</li>
        <li><strong>"User Generated Content" (UGC)</strong> refers to any display name, profile avatar photo, feedback message, or commentary uploaded or transmitted to the platform by a user.</li>
        <li><strong>"Leaderboard"</strong> means the public rankings compiled by our servers displaying user performance (XP, accuracy, level, and scores) across Daily, Weekly, and All-Time competitive intervals.</li>
    </ul>
</section>

<section id="sec3">
    <h2><span class="sec-num">03.</span> Eligibility, Legal Capacity &amp; Age Restrictions</h2>
    <p>
        The Quizs platform is intended for learners and trivia enthusiasts aged <strong>13 years or older</strong>. If you are between the ages of 13 and the age of legal majority in your country of residence (typically 18 years), you may only access the Service under the direct supervision and with the prior consent of a parent or legal guardian who agrees to be bound by these Terms.
    </p>
    <p>
        By using Quizs, you represent and warrant that:
    </p>
    <ul>
        <li>You possess the legal capacity to enter into a binding contract under the laws of your jurisdiction.</li>
        <li>You are not a resident of any country embargoed by applicable trade sanctions, nor are you listed on any prohibited party lists maintained by competent international authorities.</li>
        <li>You have not previously been banned, suspended, or disqualified from the Quizs platform for fair play violations, cheating, abuse, or unauthorized exploitation.</li>
    </ul>
</section>

<section id="sec4">
    <h2><span class="sec-num">04.</span> Account Registration, Authentication &amp; Security</h2>
    <h3>4.1 Account Creation &amp; Truthful Information</h3>
    <p>
        To access personalized features—such as tracking historical quiz scores, saving attendance streaks, ranking on leaderboards, and earning achievement badges—you must register an account. You agree to provide true, accurate, current, and complete registration information (such as your legitimate email address) and to keep your profile information updated. Creating accounts under fictitious email domains or impersonating another real individual is strictly prohibited.
    </p>

    <h3>4.2 Google Sign-In &amp; OAuth Authentication</h3>
    <p>
        Quizs facilitates frictionless authentication through the official Google Sign-In SDK. When logging in via Google OAuth, you authorize Google to transmit verified profile credentials (email, name, Google Subject ID) to our servers. You remain bound by Google's respective Terms of Service regarding the governance of your underlying Google account.
    </p>

    <h3>4.3 Credential Confidentiality &amp; Account Responsibility</h3>
    <p>
        You are solely responsible for safeguarding the secrecy of your password and credentials. You must immediately notify our security desk at <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a> if you suspect any unauthorized access, compromised passwords, or security breach concerning your account. Quizs will not be liable for any losses, penalties, or damages arising from unauthorized access resulting from your failure to maintain credential security.
    </p>

    <h3>4.4 One Account Per User &amp; No Account Transfer</h3>
    <p>
        Users are entitled to maintain one (1) primary active account. You may not sell, trade, gift, license, or transfer your Quizs account, leaderboards, virtual coins, or player progression to any other individual or entity without our prior written authorization.
    </p>
</section>

<section id="sec5">
    <h2><span class="sec-num">05.</span> Grant of Limited License &amp; Intellectual Property Rights</h2>
    <h3>5.1 Proprietary Ownership</h3>
    <p>
        The Quizs Application, including all question databases, curated translations in English and Hindi, answer keys, comprehensive explanations, graphical UI assets, Figma illustrations, sound effects, typography, brand marks, and underlying software code (Dart/Flutter, Laravel PHP, SQL schemas), is the exclusive intellectual property of <strong>Arknox</strong> and its licensors. All rights, title, and interest not expressly granted to you are reserved by the Company and protected under international copyright, trademark, and trade secret conventions.
    </p>

    <h3>5.2 Limited User License</h3>
    <p>
        Subject to your strict and ongoing compliance with these Terms, Quizs grants you a personal, revocable, non-exclusive, non-transferable, non-sublicensable, limited license to download and execute one copy of the compiled mobile application on a compatible personal device solely for non-commercial, personal educational and entertainment purposes.
    </p>

    <h3>5.3 License Restrictions</h3>
    <p>
        You expressly agree that you shall NOT, directly or indirectly:
    </p>
    <ul>
        <li>Decompile, disassemble, reverse engineer, decrypt, or attempt to derive the human-readable source code of the Flutter client or backend APIs.</li>
        <li>Copy, scrape, spider, harvest, duplicate, republish, or create derivative works from our quiz questions, translations, explanations, or artwork.</li>
        <li>Bypass, alter, defeat, or tamper with any digital rights management, security token checks, certificate pinning, or encryption mechanisms implemented in the App.</li>
        <li>Use the Application to develop a competing quiz service, database, or commercial trivia repository.</li>
    </ul>
</section>

<section id="sec6">
    <h2><span class="sec-num">06.</span> Educational Disclaimer, Trivia Accuracy &amp; Content Nuance</h2>
    <h3>6.1 For Educational &amp; Entertainment Purposes Only</h3>
    <p>
        All trivia, questions, mathematical calculations, scientific facts, historical accounts, and explanations provided within Quizs are curated in good faith for general knowledge enrichment, cognitive training, and informal learning. <strong>Quizs is not an accredited educational institution, certification authority, or academic testing agency.</strong>
    </p>

    <h3>6.2 Disclaimer of Content Accuracy</h3>
    <p>
        While our editorial and curriculum teams exert diligent efforts to verify facts, cross-reference historical dates, and maintain rigorous standards across all categories, we do not guarantee or warrant that every question, answer key, translation, or explanation is 100% complete, accurate, up-to-date, or error-free.
    </p>
    <p>
        Facts and scientific consensus evolve, and regional curricula or competitive examination boards may recognize differing interpretations of historical events or scientific theories. You agree that you will not rely upon Quizs as the sole or authoritative source of facts for official civil service examinations, academic grading, medical diagnostics, legal evaluations, or professional licensing tests.
    </p>

    <h3>6.3 Bilingual Content (English &amp; Hindi)</h3>
    <p>
        Quizs features bilingual educational content rendered in both English and Hindi. While translations are crafted to convey accurate semantic meaning and educational value, subtle linguistic variances, regional idioms, or transliteration distinctions may occur. If you discover a typographical error, factual flaw, or translation inaccuracy, you are encouraged to submit an inquiry to <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a> so our editorial team may review and patch the question dataset.
    </p>

    <h3>6.4 Copyright Infringement &amp; DMCA Notice / Takedown Procedures</h3>
    <p>
        Quizs respects the intellectual property rights of educators, publishers, authors, and creators worldwide and expects its users to do the same. If you are a copyright owner or an agent authorized to act on behalf of one, and you believe in good faith that any question, explanation, illustration, or content available on or through the Application infringes your copyrighted work, you may submit a formal notification pursuant to the <strong>Digital Millennium Copyright Act (DMCA), 17 U.S.C. § 512</strong> and international intellectual property treaties.
    </p>
    <p>
        To be effective, your formal written notice must be transmitted to our designated Copyright Agent at <a href="mailto:quizsappliaction@gmail.com?subject=DMCA%20Copyright%20Notice">quizsappliaction@gmail.com</a> and must contain substantially the following information:
    </p>
    <ol>
        <li>A physical or electronic signature of a person authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
        <li>Identification of the copyrighted work claimed to have been infringed, or, if multiple copyrighted works at a single online site are covered by a single notification, a representative list of such works.</li>
        <li>Identification of the material that is claimed to be infringing or to be the subject of infringing activity and that is to be removed or access to which is to be disabled, along with information reasonably sufficient to permit Quizs to locate the material (such as the specific Quiz Category, Topic name, Question text, and screen screenshot).</li>
        <li>Information reasonably sufficient to permit Quizs to contact you as the complaining party, such as an address, telephone number, and, if available, an electronic mail address at which you may be contacted.</li>
        <li>A statement that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law.</li>
        <li>A statement that the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
    </ol>
    <p>
        Upon receipt of a bona fide, compliant DMCA notice, Quizs will expeditiously remove or disable access to the infringing content, notify the relevant content contributor or editor, and, where applicable under our repeat infringer policy, terminate the accounts of repeat intellectual property infringers.
    </p>

</section>

<section id="sec7">
    <h2><span class="sec-num">07.</span> Fair Play, Anti-Cheating &amp; Platform Integrity Policy</h2>
    <p>
        Quizs fosters a healthy, encouraging, and competitive educational community. The integrity of our scoring algorithms, player rankings, and leaderboard rosters is sacred. We maintain a zero-tolerance policy towards cheating, exploitation, and bad-faith automation.
    </p>
    <h3>7.1 Prohibited Cheating Practices</h3>
    <p>
        You expressly covenant and warrant that you will not engage in any of the following activities:
    </p>
    <ul>
        <li><strong>Automated Scripts &amp; Bots:</strong> Utilizing headless browsers, automated tapping macros, OCR scrapers, Python scripts, or artificial intelligence screen-reading bots to automatically solve and submit quiz answers.</li>
        <li><strong>Payload Tampering &amp; API Forgery:</strong> Intercepting HTTPS network packets (via Charles Proxy, Burp Suite, or MITM tools) to inspect answer keys, alter submitted scores, manipulate completion timestamps, or forge server responses.</li>
        <li><strong>Memory Injection &amp; Client Modification:</strong> Running modified, patched, or cracked APK binaries, using memory editors (such as GameGuardian or Cheat Engine), or executing memory injections to freeze question timers or guarantee perfect accuracy.</li>
        <li><strong>Multi-Accounting &amp; Collusion:</strong> Creating multiple secondary (sockpuppet) accounts to manipulate leaderboard averages, bypass rate limits, or exploit initial bonus coins.</li>
        <li><strong>Denial of Service &amp; Server Abuse:</strong> Flooding backend endpoints with excessive concurrent requests, attempting SQL injection, or disrupting server infrastructure.</li>
    </ul>

    <h3>7.2 Enforcement &amp; Penalties</h3>
    <p>
        Our backend continuously executes automated telemetry integrity checks. If our systems detect anomalous response durations, impossible completion speeds, cryptographic signature mismatches, or verified script patterns, Quizs reserves the absolute right, in its sole discretion and without prior notice, to:
    </p>
    <ul>
        <li>Reset or recalculate your accumulated XP, accuracy percentages, and leaderboard scores.</li>
        <li>Forfeit all virtual coins, streak counts, and milestone achievements.</li>
        <li>Disqualify your account from appearing on public Daily, Weekly, or All-Time Leaderboards.</li>
        <li>Temporarily shadowban or permanently terminate your account and blacklist associated device hardware identifiers and IP ranges.</li>
    </ul>

    <h3>7.3 Question Timers, Network Latency &amp; Tie-Breaking Protocols</h3>
    <p>
        Competitive quizzes within the Application feature timed question countdowns (typically 15 to 30 seconds per question) to test agility, knowledge retention, and quick thinking:
    </p>
    <ul>
        <li><strong>Server-Side Time Authority:</strong> The authoritative timer for each quiz session is managed and verified on our server cluster. While our mobile client displays a dynamic countdown timer for user convenience, submitted answer timestamps undergo algorithmic verification on our servers to ensure that answers were received within the legitimate allowable time buffer (accounting for standard network transit latency).</li>
        <li><strong>Network Latency Disclaimer:</strong> Quizs is not responsible for answer submission failures, timed-out questions, or lost points resulting from poor cellular signal quality, Wi-Fi packet drops, local handset stutter, background app switching, or device sleep states. Users are advised to connect to stable, low-latency broadband or cellular connections before beginning competitive or tournament quiz sessions.</li>
        <li><strong>Tie-Breaking Standards:</strong> In competitive leaderboards where multiple users achieve identical numerical scores or accuracy percentages, rankings are broken algorithmically based on (1) fastest cumulative completion time, (2) longest active consecutive attendance streak, and (3) earlier chronological submission date. All server-calculated rankings are final and binding.</li>
    </ul>

</section>

<section id="sec8">
    <h2><span class="sec-num">08.</span> Virtual Economy: Coins, XP, Badges &amp; Progression</h2>
    <h3>8.1 No Real-World Monetary Value</h3>
    <p>
        Quizs features a gamified virtual economy consisting of Virtual Coins, Experience Points (XP), Player Levels, and Achievement Badges. You expressly acknowledge and agree that:
    </p>
    <ul>
        <li><strong>Zero Monetary Value:</strong> Virtual Coins, XP, and badges possess <em>zero real-world financial, monetary, or commercial value</em>. They do not constitute currency, property, electronic money, or cryptocurrency of any kind.</li>
        <li><strong>No Cash Redemption:</strong> Virtual Coins cannot be redeemed, exchanged, cashed out, or refunded for fiat currency, real-world goods, legal tender, gift cards, or physical merchandise under any circumstance.</li>
        <li><strong>Non-Transferable:</strong> Virtual Coins and player progression are strictly personal to your account and cannot be sold, bartered, gifted, or transferred to any third party. Any attempted off-platform sale or auction of Quizs accounts or virtual balances is void and constitutes grounds for immediate account termination.</li>
    </ul>

    <h3>8.2 Company's Authority Over Virtual Items</h3>
    <p>
        All Virtual Items represent a limited, revocable, non-exclusive license granted to you for entertainment within the App. Quizs retains the absolute and unilateral right to manage, regulate, adjust, recalculate, rebalance, or extinguish Virtual Coins, XP conversion ratios, level thresholds, and reward parameters at any time without prior notice or legal liability to you.
    </p>
</section>

<section id="sec9">
    <h2><span class="sec-num">09.</span> Attendance Check-Ins &amp; Daily Streaks</h2>
    <p>
        The Application features a Daily Attendance calendar and consecutive Streak tracking mechanism to incentivize regular daily study:
    </p>
    <ul>
        <li><strong>Check-in Requirement:</strong> To maintain an active streak, you must open the App and complete at least one eligible quiz or attendance action within the defined 24-hour calendar window (calculated based on UTC or your registered regional time zone).</li>
        <li><strong>Streak Loss &amp; Resets:</strong> Failing to complete the requisite check-in during a calendar window will cause the consecutive streak counter to reset to day zero, unless protected by a valid in-game streak freeze mechanic.</li>
        <li><strong>No Legal Claim for Lost Streaks:</strong> While we endeavor to maintain uninterrupted server availability, Quizs is not liable for lost streaks resulting from device battery failure, internet service provider outages, local device time clock alterations, or temporary server maintenance.</li>
    </ul>
</section>

<section id="sec10">
    <h2><span class="sec-num">10.</span> Public Leaderboards, Rankings &amp; Competitive Display</h2>
    <p>
        When you participate in quizzes, your performance statistics (including display name, chosen avatar, total XP, accuracy rate, and numerical rank) may be compiled and displayed on our public Daily, Weekly, and All-Time Leaderboards.
    </p>
    <ul>
        <li>By creating an account, you grant Quizs a perpetual, worldwide, royalty-free license to display your public display name, avatar, and quiz scores across our in-app ranking tables and promotional leaderboards.</li>
        <li>If you prefer to maintain personal privacy, you are welcome to configure an abstract display pseudonym (e.g., "QuizMaster99") and select a default illustrated avatar via <em>Profile &gt; Edit Profile</em>.</li>
        <li>We reserve the right to remove, disqualify, or reset leaderboard entries that we determine, in our sole discretion, were achieved through exploits, cheating, unauthorized automation, or inappropriate usernames.</li>
    </ul>
</section>

<section id="sec11">
    <h2><span class="sec-num">11.</span> In-App Advertisements &amp; Third-Party Promotions (AdMob)</h2>
    <p>
        To keep Quizs completely free of mandatory subscription fees, the Application integrates advertisements served by the <strong>Google Mobile Ads SDK (AdMob)</strong>:
    </p>
    <ul>
        <li><strong>Ad Formats:</strong> You will encounter banner advertisements, full-screen interstitial ads between quiz modules, and optional rewarded video ads.</li>
        <li><strong>Third-Party Content Disclaimer:</strong> Advertisements presented within the App are programmatic and delivered by Google's advertising network. Quizs does not endorse, sponsor, recommend, or guarantee the authenticity, legality, safety, or quality of any third-party products, services, games, or websites promoted in such advertisements.</li>
        <li><strong>Independent Dealings:</strong> Any correspondence, purchase, or interaction you undertake with an external advertiser encountered through our App is solely between you and that third party. Quizs shall not be held responsible or liable for any loss, damage, financial transaction, or dispute arising from your dealings with third-party advertisers.</li>
    </ul>
</section>

<section id="sec12">
    <h2><span class="sec-num">12.</span> User Conduct &amp; Prohibited Activities</h2>
    <p>
        You agree to utilize the Quizs platform strictly in accordance with all applicable local, national, and international laws and regulations. You shall NOT:
    </p>
    <ul>
        <li>Use the Application for any unlawful, fraudulent, extortionate, or malicious objective.</li>
        <li>Harass, threaten, defame, abuse, stalk, or discriminate against other users, moderators, or Arknox developers.</li>
        <li>Upload, publish, or select display names or profile avatars that contain sexually explicit imagery, hate speech, vulgarity, violent depictions, or unauthorized third-party copyrighted material.</li>
        <li>Introduce viruses, Trojan horses, worms, logic bombs, or other technologically malicious materials into our backend network.</li>
        <li>Attempt to gain unauthorized administrative access to our server databases, cloud host accounts, or user credential stores.</li>
        <li>Use automated scrapers, crawlers, or data-mining utilities to download question databases or user roster details.</li>
    </ul>
</section>

<section id="sec13">
    <h2><span class="sec-num">13.</span> User Avatars &amp; Uploaded Media (User Content)</h2>
    <p>
        If you elect to personalize your account by uploading a custom avatar image from your device's camera or photo gallery via our Edit Profile screen:
    </p>
    <ul>
        <li>You represent and warrant that you own or possess all requisite legal rights, licenses, and permissions to upload and publish the image.</li>
        <li>You grant Quizs a non-exclusive, worldwide, royalty-free license to store, resize, cache, and display your avatar within your personal profile and on public leaderboards.</li>
        <li>Quizs reserves the right, without obligation, to screen, flag, remove, or replace any avatar image that violates our content guidelines or infringes third-party intellectual property, without prior notice.</li>
    </ul>
</section>

<section id="sec14">
    <h2><span class="sec-num">14.</span> Service Availability, Software Updates &amp; Modifications</h2>
    <h3>14.1 "As Available" Operations</h3>
    <p>
        We strive to ensure continuous, reliable availability of our quiz servers. However, the Service is provided on an "AS AVAILABLE" basis. We do not guarantee that the Application will function without interruption, server lag, temporary maintenance downtime, or unexpected software bugs.
    </p>

    <h3>14.2 Software Updates &amp; Platform Evolution</h3>
    <p>
        Quizs periodically deploys updates, performance patches, new quiz categories, and security fixes via the Google Play Store and Apple App Store. Certain updates may be deemed mandatory to ensure network compatibility, fix critical security vulnerabilities, or maintain anti-cheat protocols. Failure to install the latest app updates may result in degraded functionality or an inability to connect to our API servers.
    </p>

    <h3>14.3 Right to Modify or Discontinue</h3>
    <p>
        We reserve the right, at any time and in our sole discretion, to modify, update, suspend, or discontinue any feature, category, question module, or facet of the Service, temporarily or permanently, with or without prior notice, without liability to you.
    </p>

    <h3>14.4 Beta Features, Experimental Categories &amp; Community Previews</h3>
    <p>
        From time to time, Quizs may introduce new educational categories, game modes, community question submissions, or features designated as "Beta", "Experimental", "Preview", or "Early Access".
    </p>
    <ul>
        <li>Beta features are provided strictly for testing and evaluation purposes and may contain factual inaccuracies, formatting flaws, or functional glitches.</li>
        <li>Quizs reserves the right to reset beta scores, modify question answer keys, or withdraw experimental categories at any time without liability.</li>
        <li>By participating in beta quizzes or submitting feedback, you grant Quizs an unrestricted, irrevocable, perpetual, royalty-free license to use, adapt, and implement your suggestions, bug reports, and feedback into our commercial software without any compensation or obligation to you.</li>
    </ul>

</section>

<section id="sec15">
    <h2><span class="sec-num">15.</span> Account Termination, Suspension &amp; In-App Deletion</h2>
    <h3>15.1 Termination by Quizs for Cause</h3>
    <p>
        We reserve the right, in our sole and unfettered discretion, to suspend, restrict, or permanently terminate your account and access to the Service immediately, without prior notice or liability, if:
    </p>
    <ul>
        <li>You violate any material provision of these Terms or our Privacy Policy.</li>
        <li>We detect automated scripts, bot manipulation, payload tampering, or fair play violations linked to your account.</li>
        <li>Your account demonstrates unlawful, abusive, or fraudulent behavior.</li>
        <li>We are mandated to do so by a competent court of law, regulatory agency, or government authority.</li>
    </ul>

    <h3>15.2 In-App Account Deletion by User</h3>
    <p>
        You have the unconstrained right to terminate your relationship with Quizs and permanently delete your account at any time. In compliance with Google Play Developer Policy §13.3, you can execute instant, self-service account deletion directly inside the application:
    </p>
    <div class="callout-box info">
        <strong>Self-Service Account Deletion Steps:</strong><br>
        Navigate to: <strong>Profile Screen &gt; Edit Profile &gt; Tap "Delete Account" &gt; Confirm Deletion</strong>
    </div>
    <p>
        Upon confirmation, your profile, authentication credentials, quiz attempt records, attendance streak calendar, and virtual coin balances will be permanently and irrevocably deleted from our active database. You may also submit an email deletion request to <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a>.
    </p>

    <h3>15.3 Effect of Termination</h3>
    <p>
        Upon termination of your account (whether initiated by you or by Quizs), your license to use the Application terminates immediately. All accumulated Virtual Coins, XP, levels, badges, and leaderboard rankings are permanently forfeited. Provisions of these Terms that by their nature should survive termination (including Sections 5, 16, 17, 18, 19, and 20) shall survive.
    </p>
</section>

<section id="sec16">
    <h2><span class="sec-num">16.</span> Comprehensive Disclaimer of Warranties</h2>
    <div class="callout-box warning">
        <strong>LEGAL DISCLAIMER: READ CAREFULLY</strong><br>
        TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, THE QUIZS APPLICATION, ITS CONTENT, QUESTIONS, EXPLANATIONS, TRIVIA, LEADERBOARDS, AND ALL ASSOCIATED SERVICES ARE PROVIDED ON AN <strong>"AS IS"</strong> AND <strong>"AS AVAILABLE"</strong> BASIS, WITH ALL FAULTS AND WITHOUT WARRANTY OF ANY KIND.
    </div>
    <p>
        QUIZS, AR KNOX, AND ITS OFFICERS, DIRECTORS, DEVELOPERS, EMPLOYEES, AND AFFILIATES (COLLECTIVELY, THE "QUIZS PARTIES") HEREBY EXPRESSLY DISCLAIM ALL WARRANTIES AND CONDITIONS, WHETHER EXPRESS, IMPLIED, STATUTORY, OR COLLATERAL, INCLUDING WITHOUT LIMITATION:
    </p>
    <ul>
        <li>ANY IMPLIED WARRANTIES OF MERCHANTABILITY, SATISFACTORY QUALITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.</li>
        <li>THAT THE SERVICE WILL MEET YOUR INDIVIDUAL ACADEMIC, EDUCATIONAL, OR PROFESSIONAL REQUIREMENTS.</li>
        <li>THAT THE OPERATION OF THE APPLICATION WILL BE UNINTERRUPTED, TIMELY, SECURE, ACCURATE, OR ERROR-FREE.</li>
        <li>THAT ALL QUIZ QUESTIONS, ANSWERS, TRANSLATIONS, OR EXPLANATIONS ARE COMPLETE, SCIENTIFICALLY DEFINITIVE, HISTORICALLY ACCURATE, OR FREE OF DEFECTS.</li>
        <li>THAT ANY DEFECTS, GLITCHES, OR BUGS IN THE SOFTWARE WILL BE CORRECTED.</li>
        <li>THAT THE SERVICE OR THE CLOUD SERVERS THAT MAKE IT AVAILABLE ARE FREE OF VIRUSES, MALWARE, OR OTHER HARMFUL COMPONENTS.</li>
    </ul>
</section>

<section id="sec17">
    <h2><span class="sec-num">17.</span> Limitation of Liability &amp; Cap on Damages</h2>
    <div class="callout-box warning">
        <strong>LIMITATION OF LIABILITY: READ CAREFULLY</strong><br>
        TO THE MAXIMUM EXTENT PERMITTED UNDER APPLICABLE LAW, IN NO EVENT SHALL THE QUIZS PARTIES BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, PUNITIVE, CONSEQUENTIAL, OR EXTRAORDINARY DAMAGES OF ANY CHARACTER WHATSOEVER.
    </div>
    <p>
        THIS INCLUDES, WITHOUT LIMITATION, DAMAGES FOR LOSS OF PROFITS, LOSS OF REVENUE, LOSS OF GOODWILL, LOSS OF DATA, DEVICE CORRUPTION, ACADEMIC OR EXAMINATION FAILURE, WORK STOPPAGE, SYSTEM CRASH, OR ANY OTHER COMMERCIAL OR PERSONAL LOSSES OR DAMAGES ARISING OUT OF OR RELATED TO YOUR USE OF OR INABILITY TO USE THE QUIZS SERVICE, REGARDLESS OF THE THEORY OF LIABILITY (WHETHER IN CONTRACT, TORT, STRICT LIABILITY, NEGLIGENCE, OR OTHERWISE), EVEN IF WE HAVE BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.
    </p>
    <p>
        IN NO EVENT SHALL OUR TOTAL AGGREGATE LIABILITY ARISING FROM OR RELATED TO ALL CLAIMS, DISPUTES, OR CAUSES OF ACTION CONNECTED WITH THESE TERMS OR YOUR USE OF THE SERVICE EXCEED THE TOTAL AMOUNT ACTUALLY PAID BY YOU TO QUIZS IN THE TWELVE (12) MONTHS PRECEDING THE CLAIM, OR <strong>FIVE HUNDRED INDIAN RUPEES (INR ₹500) / TEN UNITED STATES DOLLARS (USD $10.00)</strong>, WHICHEVER IS GREATER.
    </p>
    <p>
        SOME JURISDICTIONS DO NOT ALLOW THE EXCLUSION OR LIMITATION OF INCIDENTAL OR CONSEQUENTIAL DAMAGES OR LIMITATIONS ON HOW LONG AN IMPLIED WARRANTY LASTS, SO CERTAIN LIMITATIONS ABOVE MAY NOT APPLY TO YOU IN FULL MEASURE.
    </p>
</section>

<section id="sec18">
    <h2><span class="sec-num">18.</span> Indemnification Obligations</h2>
    <p>
        You agree to defend, indemnify, and hold harmless the Quizs Parties from and against any and all civil claims, demands, liabilities, damages, judgments, awards, losses, costs, expenses, and fees (including reasonable legal and attorneys' fees) arising out of or relating to:
    </p>
    <ul>
        <li>Your violation or breach of any term, condition, or warranty set forth in these Terms.</li>
        <li>Your misuse, fraudulent use, or illegal exploitation of the Application.</li>
        <li>Your engagement in automated cheating, botting, reverse engineering, or network tampering.</li>
        <li>Any User Content (including avatar photos or usernames) submitted or uploaded by you that infringes any third-party intellectual property, privacy, or proprietary right.</li>
        <li>Your violation of any applicable statute, regulation, or the rights of any third party.</li>
    </ul>
</section>

<section id="sec19">
    <h2><span class="sec-num">19.</span> Governing Law, Mandatory Dispute Resolution &amp; Arbitration</h2>
    <h3>19.1 Governing Law</h3>
    <p>
        These Terms and Conditions, their interpretation, validity, performance, and any disputes arising hereunder shall be governed by and construed in accordance with the substantive laws of <strong>India</strong>, without regard to its conflict of law principles. The United Nations Convention on Contracts for the International Sale of Goods is explicitly excluded.
    </p>

    <h3>19.2 Mandatory Informal Dispute Resolution</h3>
    <p>
        Before initiating any formal legal or arbitral proceeding, you and Quizs agree to engage in informal, good-faith negotiations for a minimum period of thirty (30) calendar days. To initiate informal resolution, you must transmit written notice detailing your claim, full name, registered email address, and requested remedy to <a href="mailto:quizsappliaction@gmail.com?subject=Dispute%20Notice">quizsappliaction@gmail.com</a>.
    </p>

    <h3>19.3 Binding Arbitration &amp; Jurisdiction</h3>
    <p>
        If a dispute cannot be resolved through informal negotiations within thirty (30) days, the dispute shall be finally resolved through binding arbitration administered in accordance with the Arbitration and Conciliation Act, 1996 (India) by a sole arbitrator mutually agreed upon by the parties. The seat and venue of arbitration shall be in India, and proceedings shall be conducted in English.
    </p>

    <h3>19.4 Class Action Waiver</h3>
    <div class="callout-box warning">
        <strong>CLASS ACTION WAIVER:</strong> YOU AND QUIZS EXPRESSLY AGREE THAT ALL PROCEEDINGS TO RESOLVE DISPUTES WILL BE CONDUCTED SOLELY ON AN INDIVIDUAL BASIS. NEITHER YOU NOR QUIZS WILL SEEK TO HAVE ANY DISPUTE HEARD AS A CLASS ACTION, REPRESENTATIVE PROCEEDING, OR PRIVATE ATTORNEY GENERAL ACTION.
    </div>
</section>

<section id="sec20">
    <h2><span class="sec-num">20.</span> Miscellaneous Legal Provisions</h2>
    <ul>
        <li><strong>Entire Agreement:</strong> These Terms and Conditions, together with our <a href="https://quizs.in/privacy">Privacy Policy</a>, constitute the sole and entire legal agreement between you and Quizs regarding the Application, superseding all prior oral or written negotiations, representations, or understandings.</li>
        <li><strong>Severability:</strong> If any provision of these Terms is adjudicated to be invalid, illegal, or unenforceable by an arbitrator or court of competent jurisdiction, that provision shall be enforced to the maximum extent permissible, and the remaining provisions shall continue in full force and effect.</li>
        <li><strong>No Waiver:</strong> No failure or delay by Quizs in exercising any right, remedy, power, or privilege under these Terms shall operate as a waiver thereof.</li>
        <li><strong>Assignment:</strong> You may not assign, transfer, or delegate your rights or obligations under these Terms without our prior written consent. Quizs may freely assign or transfer its rights and obligations without restriction or notice.</li>
        <li><strong>Force Majeure:</strong> Quizs shall not be held liable or responsible for any delay, performance failure, or service outage caused by events beyond our reasonable control, including acts of God, telecommunication line failures, cloud infrastructure outages, cyber warfare, DDOS attacks, pandemics, labor strikes, or governmental decrees.</li>
    </ul>

    <h3>20.2 Export Controls, Sanctions &amp; Anti-Corruption Compliance</h3>
    <p>
        The software, technical data, and underlying cryptography incorporated within the Quizs mobile application are subject to the export control and economic sanctions laws of India, the United States (including the Export Administration Regulations - EAR maintained by the U.S. Department of Commerce and sanctions administered by the Office of Foreign Assets Control - OFAC), the European Union, and other applicable sovereign jurisdictions.
    </p>
    <p>
        By installing and using the Application, you represent and warrant that you are not located in, under the control of, or a national or resident of any embargoed or sanctioned territory (including Cuba, Iran, North Korea, Syria, or the Crimea/Donetsk/Luhansk regions of Ukraine) and that you are not named on any restricted party list. You agree not to export, re-export, transfer, or facilitate the diversion of the software in violation of any applicable export control laws.
    </p>

    <h3>20.3 Electronic Signatures, Notices &amp; Consent Delivery</h3>
    <p>
        You consent to the receipt of communications from Quizs in electronic form, including electronic mail transmissions sent to your registered address and in-app alert notices. You agree that all agreements, notices, disclosures, and other communications that we provide to you electronically satisfy any legal requirement that such communications would satisfy if they were in a hardcopy writing. Your affirmative click or tap of buttons labeled "Sign In", "Sign Up", "Accept", or "Continue" constitutes your lawful electronic signature under the Electronic Signatures in Global and National Commerce Act (E-SIGN Act), the Indian Information Technology Act, 2000, and equivalent international statutes.
    </p>

</section>

<section id="sec21">
    <h2><span class="sec-num">21.</span> Amendments, Notices &amp; Contact Information</h2>
    <p>
        We reserve the right to revise, update, or replace these Terms and Conditions at our sole discretion. When material changes are implemented, we will provide advance notice through the Application (such as an alert modal or banner) or via electronic transmission to your registered email address.
    </p>
    <p>
        The date of the most recent revision will always be visible at the top of this document. Your continued access to or utilization of the Quizs platform following the effective date of an amended version constitutes your binding acceptance of the updated Terms.
    </p>
    <p>
        For inquiries, formal notices, or legal questions concerning these Terms, please reach out to our legal compliance desk:
    </p>
    <div class="callout-box">
        <strong>Quizs Legal &amp; Compliance Office:</strong><br>
        Application: <strong>Quizs (Bilingual Educational Trivia App)</strong><br>
        Developer &amp; Publisher: <strong>Arknox Development Team</strong><br>
        Email: <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a><br>
        Website: <a href="https://quizs.in">https://quizs.in</a><br>
        Terms URL: <a href="https://quizs.in/terms">https://quizs.in/terms</a><br>
        Privacy URL: <a href="https://quizs.in/privacy">https://quizs.in/privacy</a>
    </div>
</section>
@endsection
