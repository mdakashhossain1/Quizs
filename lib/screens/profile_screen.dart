import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../l10n/app_strings.dart';
import '../services/app_review_service.dart';
import '../services/auth_service.dart';
import '../services/profile_stats_service.dart';
import '../services/sound_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';
import '../widgets/shimmer_loading.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _soundEnabled = true;
  bool _notificationEnabled = true;
  ProfileStats _stats = ProfileStats.empty;
  bool _statsLoading = true;

  @override
  void initState() {
    super.initState();
    AppLanguage.instance.addListener(_onLanguageChanged);
    _loadStats();
    _loadSoundPreference();
  }

  Future<void> _loadSoundPreference() async {
    final enabled = await SoundService.instance.isEnabled();
    if (mounted) setState(() => _soundEnabled = enabled);
  }

  @override
  void dispose() {
    AppLanguage.instance.removeListener(_onLanguageChanged);
    super.dispose();
  }

  void _onLanguageChanged() {
    if (mounted) setState(() {});
  }

  Future<void> _loadStats() async {
    try {
      final stats = await ProfileStatsService.instance.fetch();
      if (mounted) setState(() { _stats = stats; _statsLoading = false; });
    } catch (_) {
      if (mounted) setState(() => _statsLoading = false);
    }
  }

  // Dedicated Quizs-branded pages served by the Laravel backend (see
  // admin/resources/views/legal/ and admin/routes/web.php) — these used to
  // point at Google's own generic policies.google.com pages, which describe
  // Google's data practices, not this app's.
  static const String _privacyPolicyUrl = 'https://quizs.in/privacy';
  static const String _termsConditionsUrl = 'https://quizs.in/terms';

  Future<void> _openPrivacyPolicy() async {
    final uri = Uri.parse(_privacyPolicyUrl);
    try {
      final launched = await launchUrl(uri, mode: LaunchMode.inAppBrowserView);
      if (!launched && mounted) _showPrivacyPolicyFallback();
    } catch (_) {
      if (mounted) _showPrivacyPolicyFallback();
    }
  }

  Future<void> _openTermsConditions() async {
    final uri = Uri.parse(_termsConditionsUrl);
    try {
      final launched = await launchUrl(uri, mode: LaunchMode.inAppBrowserView);
      if (!launched && mounted) _showTermsDialog();
    } catch (_) {
      if (mounted) _showTermsDialog();
    }
  }

  static const String _privacyPolicyText = '''
Effective Date: September 19, 2026

Quizs ("we", "us", "our") is a bilingual (English & Hindi) educational trivia and quiz mobile application operated by Arknox (package: com.quizs.application.arknox, domain: https://quizs.in). We are committed to safeguarding your personal data in full compliance with GDPR, CCPA/CPRA, India DPDPA 2023, and Google Play Developer Policies.

1. INFORMATION WE COLLECT
• Account Credentials: Name, email address, password hash (bcrypt), and optional profile avatar via camera/gallery (image_picker).
• Google Sign-In (OAuth): Verified email, display name, Google Subject ID, and public avatar URL.
• Gameplay & Learning Metrics: Quizzes attempted, questions answered, response timestamps, correctness, chosen options, accuracy rates, accumulated XP, player level, and virtual coins.
• Attendance & Streaks: Daily check-in dates, consecutive streak counts, missed days, and bonus milestones.
• Leaderboard Data: Public display name, avatar, XP, accuracy, and level for Daily, Weekly, and All-Time global rankings.
• Device & Hardware Telemetry: Device model, manufacturer, OS version, screen metrics, language/locale, and time zone.
• Push Notification Identifiers: Firebase Cloud Messaging (FCM) device registration tokens.
• Diagnostics & Crash Reporting: Anonymous stack traces, crash logs, and memory state via Firebase Crashlytics.

2. PURPOSES OF PROCESSING
• Delivering interactive quiz gameplay, real-time scoring, bilingual translations, and comprehensive explanations.
• Maintaining player level progression, attendance check-in streaks, and public leaderboard rankings.
• Sending opt-in notifications for daily study reminders, streak freeze alerts, and new educational topics.
• Diagnosing crashes, patching software bugs, and protecting platform security against bots, cheating, and unauthorized API tampering.

3. AD-FREE PLATFORM
Quizs is a completely ad-free educational application. We do not display banner ads, interstitial pop-ups, or rewarded video advertisements, and we do not collect, track, or share advertising identifiers (such as GAID or IDFA).

4. THIRD-PARTY SUBPROCESSORS
We partner only with vetted industry leaders under strict Data Processing Agreements:
• Google Firebase (Authentication, FCM push notifications, Crashlytics).
• Cloud Hosting & Database Infrastructure (Encrypted at rest with AES-256).

5. DATA RETENTION & SECURITY
• Data in Transit: TLS 1.3 encryption across all HTTPS endpoints.
• Data on Device: Session JWT tokens stored in hardware-backed keystores via FlutterSecureStorage.
• Retention: Active account data is retained while your account remains open. Crashlytics logs purge after 90 days.

6. IN-APP ACCOUNT DELETION (PLAY STORE §13.3)
You have the permanent right to delete your account and all associated data:
Go to Profile > Edit Profile > Tap "Delete Account" > Confirm Deletion.
All user records, quiz history, streak progress, and authentication tokens are immediately and permanently eradicated from our databases. You can also email quizsappliaction@gmail.com.

7. YOUR GLOBAL RIGHTS (GDPR / CCPA / DPDPA)
You have the right to access, rectify, restrict, export, or erase your personal data.

8. CHILDREN'S PRIVACY
Quizs is intended for a general audience aged 13+. We do not knowingly collect personal data from children under 13.

For the full 5,000+ word Privacy Policy, visit: https://quizs.in/privacy
Contact our Data Protection Officer: quizsappliaction@gmail.com
''';

  static const String _termsConditionsText = '''
Effective Date: September 19, 2026

By creating an account, signing in, or playing Quizs, you enter into a legally binding agreement with Arknox governed by these Terms and Conditions.

1. YOUR ACCOUNT & ELIGIBILITY
• You must be at least 13 years old to use the Service. Minors between 13 and 18 require parental or legal guardian consent.
• You must provide accurate registration details (valid email) and maintain the confidentiality of your password.
• You are solely responsible for all activities occurring under your account. You may not sell, transfer, or share your account.

2. FAIR PLAY & ANTI-CHEATING POLICY
Quizs strictly enforces fair play on all competitive leaderboards:
• Prohibited: Automated scripts, tapping bots, screen scrapers, memory injectors, modified APK binaries, packet tampering (MITM proxies), and multi-accounting (sockpuppets).
• Penalties: Accounts detected using automated cheats or exploits will face immediate score resets, streak forfeiture, leaderboard bans, and permanent account termination.

3. VIRTUAL ECONOMY: COINS, XP & STREAKS
• Zero Monetary Value: Virtual Coins, XP, Level progression, and Attendance Streaks have NO cash, cryptocurrency, or real-world monetary value.
• No Redemption or Refunds: Virtual Coins cannot be redeemed, sold, refunded, or cashed out for fiat currency or physical merchandise.
• Limited License: Virtual Items are licensed to you on a revocable, non-exclusive basis. Quizs reserves the right to rebalance or adjust progression algorithms at any time without liability.

4. EDUCATIONAL CONTENT & TRIVIA DISCLAIMER
• Questions, answers, hints, and explanations in English and Hindi are provided for general educational, cognitive training, and entertainment purposes only.
• Quizs is not an accredited academic institution. We make no warranty that trivia content is error-free, legally definitive, or suitable for professional/civil service exams.

5. INTELLECTUAL PROPERTY & LIMITED LICENSE
• All question databases, translations, illustrations, brand trademarks, sound effects, and software code are the exclusive intellectual property of Arknox.
• You are granted a limited, personal, non-commercial, revocable license to use the app on personal devices. Reverse engineering or scraping is strictly prohibited.

6. AD-FREE ENVIRONMENT
• Quizs is dedicated to an uninterrupted educational experience and is completely ad-free. The application does not serve commercial advertisements or collect advertising IDs.

7. TERMINATION & IN-APP DELETION
• You may terminate your account at any time via Profile > Edit Profile > Delete Account.
• Quizs reserves the right to suspend or terminate accounts for terms violations, cheating, or abusive conduct without prior notice.

8. DISCLAIMER OF WARRANTIES & LIABILITY LIMITATION
• The Service is provided strictly "AS IS" and "AS AVAILABLE" without warranties of any kind.
• To the maximum extent permitted by law, the Quizs Parties shall not be liable for indirect, incidental, punitive, or consequential damages. Maximum aggregate liability is capped at INR ₹500 or USD \$10.00.

9. GOVERNING LAW & ARBITRATION
• These Terms are governed by the laws of India. Disputes shall be resolved through mandatory 30-day informal negotiation, followed by binding individual arbitration. Class action proceedings are waived.

For the full 5,000+ word Terms of Service, visit: https://quizs.in/terms
Legal Inquiries: quizsappliaction@gmail.com
''';

  void _showPrivacyPolicyFallback() {
    showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(AppStrings.t('privacy_policy')),
        content: const SingleChildScrollView(
          child: Text(
            _privacyPolicyText,
            style: TextStyle(fontFamily: 'Poppins', fontSize: 12.0, height: 1.45),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => launchUrl(
              Uri.parse(_privacyPolicyUrl),
              mode: LaunchMode.externalApplication,
            ),
            child: const Text('Open Full Web Policy',
                style: TextStyle(color: QuizColors.purple, fontWeight: FontWeight.w700)),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Close', style: TextStyle(color: Colors.grey)),
          ),
        ],
      ),
    );
  }

  void _openEditProfile() {
    Navigator.of(context).pushNamed('/edit-profile');
  }

  void _showLanguageSelector() {
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => SafeArea(
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 38,
                  height: 4,
                  margin: const EdgeInsets.only(top: 12, bottom: 18),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE2DCE8),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 22),
                child: Text(
                  AppStrings.t('select_language'),
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1E1E1E),
                    letterSpacing: -0.2,
                  ),
                ),
              ),
              const SizedBox(height: 14),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildLanguageOption(
                      code: 'EN',
                      title: 'English',
                      subtitle: 'English (Default)',
                      isSelected: AppLanguage.instance.language == AppLanguageType.english,
                      onTap: () {
                        AppLanguage.instance.setLanguage(AppLanguageType.english);
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(height: 10),
                    _buildLanguageOption(
                      code: 'हि',
                      title: 'हिंदी (Hindi)',
                      subtitle: 'हिन्दी भाषा',
                      isSelected: AppLanguage.instance.language == AppLanguageType.hindi,
                      onTap: () {
                        AppLanguage.instance.setLanguage(AppLanguageType.hindi);
                        Navigator.of(context).pop();
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLanguageOption({
    required String code,
    required String title,
    required String subtitle,
    required bool isSelected,
    required VoidCallback onTap,
  }) => Material(
    color: isSelected ? const Color(0xFFF8F3FC) : Colors.white,
    borderRadius: BorderRadius.circular(14),
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? QuizColors.purple : const Color(0xFFECE5F2),
            width: isSelected ? 1.5 : 1.0,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: isSelected
                    ? const Color(0xFF6703BF).withValues(alpha: 0.12)
                    : const Color(0xFFF3EEF8),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Center(
                child: Text(
                  code,
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                    color: QuizColors.purple,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w600,
                      fontSize: 14.5,
                      color: isSelected ? QuizColors.purple : const Color(0xFF222222),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 12,
                      fontWeight: FontWeight.w400,
                      color: Color(0xFF757575),
                    ),
                  ),
                ],
              ),
            ),
            Container(
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isSelected ? QuizColors.purple : Colors.transparent,
                border: Border.all(
                  color: isSelected ? QuizColors.purple : const Color(0xFFCBC4D6),
                  width: 1.5,
                ),
              ),
              child: isSelected
                  ? const Icon(Icons.check, size: 14, color: Colors.white)
                  : null,
            ),
          ],
        ),
      ),
    ),
  );

  void _showTermsDialog() {
    showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(AppStrings.t('terms_conditions')),
        content: const SingleChildScrollView(
          child: Text(
            _termsConditionsText,
            style: TextStyle(fontFamily: 'Poppins', fontSize: 12.0, height: 1.45),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => launchUrl(
              Uri.parse(_termsConditionsUrl),
              mode: LaunchMode.externalApplication,
            ),
            child: const Text('Open Full Web Terms',
                style: TextStyle(color: QuizColors.purple, fontWeight: FontWeight.w700)),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Close', style: TextStyle(color: Colors.grey)),
          ),
        ],
      ),
    );
  }

  void _handleLogout() {
    showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Log Out'),
        content: const Text('Are you sure you want to log out of your account?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () async {
              await AuthService.instance.logout();
              if (context.mounted) {
                Navigator.of(context).pop();
                Navigator.pushNamedAndRemoveUntil(context, '/signin', (route) => false);
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: QuizColors.purple,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.logout_rounded, size: 16, color: Colors.white),
                const SizedBox(width: 6),
                Text(AppStrings.t('log_out')),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) => DesignCanvas(
    adTop: 760,
    adBefore: 890,
    color: Colors.white,
    topColor: Colors.white,
    bottomNav: QuizBottomNav(
      initialIndex: 3,
      onTabSelected: (index) {
        if (index == 0) {
          Navigator.pushNamedAndRemoveUntil(context, '/', (r) => false);
        } else if (index == 1) {
          Navigator.pushReplacementNamed(context, '/categories');
        } else if (index == 2) {
          Navigator.pushReplacementNamed(context, '/attendance');
        }
      },

    ),
    children: [
      // Top Navigation Header
      label(
        AppStrings.t('profile'),
        80,
        40,
        18,
        width: 252,
        align: TextAlign.center,
        color: QuizColors.purple,
        weight: FontWeight.w700,
      ),
      backButton(context, purple: true),

      // Profile Avatar with a ring showing today's daily-target progress
      // (roadmap §8.1 — distinct from Level and Accuracy below).
      asset('75-2_imgProfile6.png', 155, 106, 101, 101),
      at(
        144,
        95,
        123,
        123,
        DailyProgressRing(progress: _stats.todayProgressPercentage / 100),
      ),

      // Level Badge (Left) — accumulated progression from daily-target
      // performance (roadmap §8.2), not today's progress.
      panel(52, 130, 53, 53, QuizColors.purple, radius: 30),
      label('${_stats.level}', 52, 135, 24, width: 53, align: TextAlign.center, weight: FontWeight.w700, color: Colors.white),
      label(AppStrings.t('level_label'), 52, 158, 12, width: 53, align: TextAlign.center, weight: FontWeight.w500, color: Colors.white),

      // Accuracy Badge (Right) — correct-answer rate from real attempt data
      // (roadmap §8.3), separate from both the ring and the Level.
      panel(307, 130, 53, 53, QuizColors.purple, radius: 30),
      label('${_stats.accuracy.round()}%', 307, 140, 16, width: 53, align: TextAlign.center, weight: FontWeight.w700, color: Colors.white),
      label(AppStrings.t('accuracy_label'), 307, 158, 9, width: 53, align: TextAlign.center, weight: FontWeight.w500, color: Colors.white),

      // User Name with pencil edit icon
      at(
        0,
        230,
        412,
        26,
        Center(
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                AuthService.instance.userName,
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: QuizColors.purple,
                ),
              ),
              const SizedBox(width: 6),
              GestureDetector(
                onTap: _openEditProfile,
                child: CustomPaint(
                  size: const Size(13, 13),
                  painter: const _PencilPainter(color: Color(0xFF9E9E9E)),
                ),
              ),
            ],
          ),
        ),
      ),

      // Divider Line
      panel(24, 266, 364, 1, const Color(0xFFEAE5F2)),

      // Stats Row
      at(
        24,
        274,
        364,
        56,
        AnimatedSection(
          delay: const Duration(milliseconds: 60),
          child: _statsLoading
              ? const ProfileStatsShimmer()
              : Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _StatColumn(
                      value: '${_stats.quizPlayed}',
                      label: AppStrings.t('quiz_played_stat'),
                      valueColor: QuizColors.purple,
                    ),
                    _StatColumn(
                      value: '${_stats.right}',
                      label: AppStrings.t('right_stat'),
                      valueColor: const Color(0xFF10BA65),
                    ),
                    _StatColumn(
                      value: '${_stats.wrong}',
                      label: AppStrings.t('wrong_stat'),
                      valueColor: const Color(0xFFFF0000),
                    ),
                    _StatColumn(
                      value: '${_stats.thisMonth}',
                      label: AppStrings.t('this_month_stat'),
                      valueColor: QuizColors.purple,
                    ),
                  ],
                ),
        ),
      ),

      // Menu Option 1: Edit Profile
      at(
        34,
        344,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 100),
          child: _MenuTile(
            title: AppStrings.t('edit_profile'),
            onTap: _openEditProfile,
            trailing: CustomPaint(
              size: const Size(14, 14),
              painter: const _PencilPainter(color: Color(0xFF8E8E93)),
            ),
          ),
        ),
      ),

      // Menu Option 2: Language
      at(
        34,
        394,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 130),
          child: _MenuTile(
            title: '${AppStrings.t('language')} (${AppLanguage.instance.displayName})',
            onTap: _showLanguageSelector,
            trailing: CustomPaint(
              size: const Size(14, 10),
              painter: const _ChevronDownPainter(color: QuizColors.purple),
            ),
          ),
        ),
      ),

      // Menu Option 3: Sound
      at(
        34,
        444,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 160),
          child: _MenuTile(
            title: AppStrings.t('sound'),
            trailing: _CustomToggle(
              value: _soundEnabled,
              onChanged: (val) {
                setState(() => _soundEnabled = val);
                SoundService.instance.setEnabled(val);
                if (val) SoundService.instance.playClick();
              },
            ),
          ),
        ),
      ),

      // Menu Option 4: Notification
      at(
        34,
        494,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 190),
          child: _MenuTile(
            title: AppStrings.t('notification'),
            onTap: () => Navigator.of(context).pushNamed('/notifications'),
            trailing: _CustomToggle(
              value: _notificationEnabled,
              onChanged: (val) => setState(() => _notificationEnabled = val),
            ),
          ),
        ),
      ),

      // Menu Option 5: Rate & Review App
      at(
        34,
        544,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 210),
          child: _MenuTile(
            title: AppStrings.t('rate_review'),
            onTap: () => AppReviewService.instance.requestReview(),
            trailing: const Icon(
              Icons.star_rounded,
              size: 20,
              color: Color(0xFFFFB800),
            ),
          ),
        ),
      ),

      // Menu Option 6: Terms & Conditions (Opens in in-app Chrome Custom Tab)
      at(
        34,
        594,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 230),
          child: _MenuTile(
            title: AppStrings.t('terms_conditions'),
            onTap: _openTermsConditions,
            trailing: CustomPaint(
              size: const Size(14, 14),
              painter: const _ExternalLinkPainter(color: QuizColors.purple),
            ),
          ),
        ),
      ),

      // Menu Option 7: Privacy Policy (Opens in in-app Chrome Custom Tab)
      at(
        34,
        644,
        344,
        42,
        AnimatedSection(
          delay: const Duration(milliseconds: 250),
          child: _MenuTile(
            title: AppStrings.t('privacy_policy'),
            onTap: _openPrivacyPolicy,
            trailing: CustomPaint(
              size: const Size(14, 14),
              painter: const _ExternalLinkPainter(color: QuizColors.purple),
            ),
          ),
        ),
      ),

      // Log Out Button
      at(
        116,
        698,
        180,
        44,
        AnimatedSection(
          delay: const Duration(milliseconds: 280),
          child: Semantics(
            button: true,
            label: AppStrings.t('log_out'),
            child: ElevatedButton(
              onPressed: _handleLogout,
              style: ElevatedButton.styleFrom(
                backgroundColor: QuizColors.purple,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(22),
                ),
                shadowColor: QuizColors.purple.withValues(alpha: 0.3),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.logout_rounded,
                    size: 18,
                    color: Colors.white,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    AppStrings.t('log_out'),
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 14.5,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    ],
  );
}

class _StatColumn extends StatelessWidget {
  const _StatColumn({
    required this.value,
    required this.label,
    required this.valueColor,
  });

  final String value;
  final String label;
  final Color valueColor;

  @override
  Widget build(BuildContext context) => Column(
    mainAxisSize: MainAxisSize.min,
    children: [
      Text(
        value,
        style: TextStyle(
          fontFamily: 'Poppins',
          fontSize: 22,
          fontWeight: FontWeight.w700,
          color: valueColor,
          height: 1.0,
        ),
      ),
      const SizedBox(height: 2),
      Text(
        label,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
        style: const TextStyle(
          fontFamily: 'Poppins',
          fontSize: 11,
          fontWeight: FontWeight.w400,
          color: Color(0x70000000),
          height: 1.1,
        ),
      ),
    ],
  );
}

class _MenuTile extends StatelessWidget {
  const _MenuTile({
    required this.title,
    this.trailing,
    this.onTap,
  });

  final String title;
  final Widget? trailing;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) => Semantics(
    button: true,
    label: title,
    child: Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Container(
          height: 42,
          padding: const EdgeInsets.symmetric(horizontal: 18),
          decoration: BoxDecoration(
            color: const Color(0xFFF7F3FA),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: const Color(0x0C6703BF)),
          ),
          child: Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF1E1E1E),
                  ),
                ),
              ),
              ?trailing,
            ],
          ),
        ),
      ),
    ),
  );
}

class _CustomToggle extends StatelessWidget {
  const _CustomToggle({required this.value, required this.onChanged});

  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: () => onChanged(!value),
    child: AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      width: 40,
      height: 22,
      padding: const EdgeInsets.all(2.5),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(11),
        color: value ? const Color(0xFFE5DBF5) : const Color(0xFFE0E0E0),
      ),
      child: AnimatedAlign(
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeInOut,
        alignment: value ? Alignment.centerRight : Alignment.centerLeft,
        child: Container(
          width: 17,
          height: 17,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: value ? QuizColors.purple : const Color(0xFF9E9E9E),
          ),
        ),
      ),
    ),
  );
}

class _PencilPainter extends CustomPainter {
  const _PencilPainter({this.color = const Color(0xFF7A7A7A)});
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.3
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final w = size.width;
    final h = size.height;

    final path = Path()
      ..moveTo(w * 0.75, h * 0.15)
      ..lineTo(w * 0.88, h * 0.28)
      ..lineTo(w * 0.32, h * 0.84)
      ..lineTo(w * 0.12, h * 0.88)
      ..lineTo(w * 0.16, h * 0.68)
      ..close();

    canvas.drawPath(path, paint);
    canvas.drawLine(
      Offset(w * 0.62, h * 0.28),
      Offset(w * 0.74, h * 0.40),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant _PencilPainter oldDelegate) =>
      oldDelegate.color != color;
}

class _ChevronDownPainter extends CustomPainter {
  const _ChevronDownPainter({this.color = QuizColors.purple});
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.8
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final w = size.width;
    final h = size.height;

    final path = Path()
      ..moveTo(w * 0.15, h * 0.3)
      ..lineTo(w * 0.5, h * 0.7)
      ..lineTo(w * 0.85, h * 0.3);

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _ChevronDownPainter oldDelegate) =>
      oldDelegate.color != color;
}

class _ExternalLinkPainter extends CustomPainter {
  const _ExternalLinkPainter({this.color = QuizColors.purple});
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final w = size.width;
    final h = size.height;

    final boxPath = Path()
      ..moveTo(w * 0.45, h * 0.18)
      ..lineTo(w * 0.18, h * 0.18)
      ..lineTo(w * 0.18, h * 0.82)
      ..lineTo(w * 0.82, h * 0.82)
      ..lineTo(w * 0.82, h * 0.55);
    canvas.drawPath(boxPath, paint);

    canvas.drawLine(
      Offset(w * 0.45, h * 0.55),
      Offset(w * 0.82, h * 0.18),
      paint,
    );
    canvas.drawLine(
      Offset(w * 0.58, h * 0.18),
      Offset(w * 0.82, h * 0.18),
      paint,
    );
    canvas.drawLine(
      Offset(w * 0.82, h * 0.18),
      Offset(w * 0.82, h * 0.42),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant _ExternalLinkPainter oldDelegate) =>
      oldDelegate.color != color;
}
