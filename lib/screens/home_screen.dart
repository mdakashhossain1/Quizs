import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/app_update_service.dart';
import '../services/auth_service.dart';
import '../services/profile_stats_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/illustration.dart';
import '../widgets/quiz_bottom_nav.dart';
import '../widgets/shimmer_loading.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  ProfileStats _stats = ProfileStats.empty;
  bool _statsLoading = true;

  @override
  void initState() {
    super.initState();
    _loadStats();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      AppUpdateService.instance.checkForUpdate();
    });
  }

  Future<void> _loadStats() async {
    try {
      final stats = await ProfileStatsService.instance.fetch();
      if (mounted) setState(() { _stats = stats; _statsLoading = false; });
    } catch (_) {
      if (mounted) setState(() => _statsLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: AppLanguage.instance,
    builder: (context, _) => DesignCanvas(
      adTop: 776,
      adBefore: 841,
      color: const Color(0xFFFCFAFE),
      bottomNav: QuizBottomNav(
        initialIndex: 0,
        onTabSelected: (index) {
          if (index == 1) {
            Navigator.pushNamed(context, '/categories');
          } else if (index == 2) {
            Navigator.pushNamed(context, '/attendance');
          } else if (index == 3) {
            Navigator.pushNamed(context, '/profile');
          }
        },
      ),
      children: [
        at(
          188,
          391,
          283.291,
          609.491,
          Center(
            child: SizedBox(
              width: 249,
              height: 596,
              child: Transform.rotate(
                angle: 3.34 * math.pi / 180,
                child: const OutlineWord(
                  text: '?',
                  size: 500,
                  family: 'HappySchool',
                  color: Color(0x4044ED77),
                ),
              ),
            ),
          ),
        ),
        at(
          -83,
          359,
          258.586,
          383.339,
          Center(
            child: SizedBox(
              width: 137.1,
              height: 357.6,
              child: Transform.rotate(
                angle: -21.46 * math.pi / 180,
                child: const OutlineWord(
                  text: '?',
                  size: 300,
                  family: 'HappySchool',
                  color: Color(0x286900C5),
                ),
              ),
            ),
          ),
        ),
        at(0, -89, 412, 467, const PurpleHeader(home: true)),
        label(
          'Quizs',
          28,
          29,
          32,
          family: 'Quizlo',
          color: Colors.white,
          lineHeight: 1.44,
        ),
        asset(
          '1-2_imgNotification11.png',
          349,
          39,
          25,
          25,
          color: Colors.white,
        ),
        at(
          336,
          27,
          48,
          48,
          DesignAction(
            label: 'Notifications',
            onTap: () => Navigator.of(context).pushNamed('/notifications'),
          ),
        ),
        panel(48, 90, 318, 82, Colors.white, radius: 13),
        label(
          AppStrings.t('welcome'),
          71,
          109,
          15,
          family: 'Quicksand',
          lineHeight: 1.25,
        ),
        // Show only first name; auto-shrink font if it's still long.
        at(
          66,
          116,
          170,
          44,
          FittedBox(
            fit: BoxFit.scaleDown,
            alignment: Alignment.centerLeft,
            child: Text(
              AuthService.instance.userName
                  .trim()
                  .split(RegExp(r'\s+'))
                  .first,
              style: const TextStyle(
                fontFamily: 'Quizlo',
                fontSize: 32,
                color: Color(0xFF6900C5),
                height: 1.44,
              ),
            ),
          ),
        ),
        label(
          '${AppStrings.t('level_label')} ${_stats.level}',
          252,
          113,
          24,
          weight: FontWeight.w800,
          color: const Color(0xFF8400B1),
        ),
        // Tap the welcome panel to open the profile screen.
        action(context, 'Profile', '/profile', 48, 90, 318, 82),
        at(
          47,
          204,
          139,
          142,
          AnimatedSection(
            delay: const Duration(milliseconds: 50),
            child: _statsLoading
                ? const HomeRankCardShimmer()
                : _RankCard(rank: _stats.rank),
          ),
        ),
        at(
          227,
          204,
          139,
          142,
          AnimatedSection(
            delay: const Duration(milliseconds: 80),
            child: _statsLoading
                ? const HomeRankCardShimmer()
                : _RankCard(rank: _stats.rank, achievement: true),
          ),
        ),
        action(context, 'Leaderboard', '/results', 47, 204, 139, 142),
        action(context, 'Achievements', '/achievements', 227, 204, 139, 142),
        label(
          AppStrings.t('quiz_category'),
          36,
          405,
          20,
          weight: FontWeight.w800,
          color: QuizColors.purple,
        ),
        action(context, 'Browse categories', '/categories', 36, 405, 340, 30),
        at(20, 448, 372, 105, const HomeCategoriesRow()),
        at(
          37,
          576,
          337,
          155,
          const AnimatedSection(
            delay: Duration(milliseconds: 140),
            child: _DailyChallenge(),
          ),
        ),
        action(context, 'Join a Quiz', '/categories', 37, 576, 337, 155),
      ],
    ),
  );
}

class _RankCard extends StatelessWidget {
  const _RankCard({required this.rank, this.achievement = false});

  /// Backend-computed leaderboard position (roadmap §12); 0 means not
  /// loaded yet.
  final int rank;
  final bool achievement;

  @override
  Widget build(BuildContext context) => Stack(
    clipBehavior: Clip.none,
    children: [
      panel(0, 9, 139, 133, Colors.white, radius: 31),
      at(
        5,
        14,
        129,
        75,
        ClipRRect(
          borderRadius: const BorderRadius.vertical(
            top: Radius.circular(29),
            bottom: Radius.circular(8),
          ),
          child: DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: achievement
                    ? const Alignment(.571, -1.488)
                    : const Alignment(.524, -1.494),
                end: achievement
                    ? const Alignment(-.571, 1.488)
                    : const Alignment(-.524, 1.494),
                stops: achievement
                    ? const [.26583, .93818]
                    : const [.25472, .96427],
                colors: achievement
                    ? [const Color(0xFFE0FF54), const Color(0xFF68C814)]
                    : [const Color(0xFFBF00FF), const Color(0xFF730099)],
              ),
            ),
            child: Stack(
              children: [
                at(
                  -37,
                  achievement ? -97 : -102,
                  203,
                  203,
                  Opacity(
                    opacity: achievement ? .41 : .16,
                    child: const FigmaAsset('1-2_imgRadialLines2.png'),
                  ),
                ),
                at(
                  0,
                  68,
                  129,
                  7,
                  const DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Color(0x00000000), Color(0x40000000)],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      at(
        achievement ? 42 : 37,
        achievement ? 0 : 3,
        achievement ? 56 : 66,
        achievement ? 56 : 66,
        Illustration(
          achievement ? '1-2_imgTrophy21.png' : '1-2_imgRank1.png',
          shadows: [
            BoxShadow(
              offset: achievement ? const Offset(-2, 2) : const Offset(-1, 0),
              blurRadius: achievement ? 4 : 2.3,
              color: const Color(0x40000000),
            ),
          ],
          innerShadows: achievement
              ? [
                  const BoxShadow(
                    offset: Offset(0, 4),
                    blurRadius: 4,
                    color: Color(0x40000000),
                  ),
                ]
              : [
                  const BoxShadow(
                    offset: Offset(-2, 2),
                    blurRadius: 2.2,
                    color: Color(0xB3FFFFFF),
                  ),
                  const BoxShadow(
                    offset: Offset(2, -2),
                    blurRadius: 2.1,
                    color: Color(0x5C000000),
                  ),
                ],
        ),
      ),
      label(
        achievement ? AppStrings.t('achievement') : AppStrings.t('leaderboard'),
        achievement ? 31 : 27,
        67,
        12,
        weight: FontWeight.w600,
        color: achievement ? Colors.black : Colors.white,
      ),
      label(
        rank > 0 ? '${AppStrings.t('rank_label')} $rank' : '',
        27,
        98,
        20,
        weight: FontWeight.w800,
        color: achievement ? const Color(0xFF8BBE00) : const Color(0xFFBF00FF),
      ),
    ],
  );
}

class _DailyChallenge extends StatelessWidget {
  const _DailyChallenge();

  @override
  Widget build(BuildContext context) => ClipRRect(
    borderRadius: BorderRadius.circular(15),
    child: Stack(
      children: [
        const Positioned.fill(
          child: DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0xFF5A0096), Color(0xFF9100FF)],
              ),
            ),
          ),
        ),
        at(
          -113,
          -390,
          618,
          619,
          const ColorFiltered(
            colorFilter: ColorFilter.mode(Color(0xFF7900CD), BlendMode.color),
            child: FigmaAsset(
              '1-2_img73274TeachingSchoolElementsLearningDownloadHqPng2.png',
            ),
          ),
        ),
        at(
          0,
          147,
          337,
          8,
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0x00480060), Color(0xFF480060)],
              ),
            ),
          ),
        ),
        label(
          AppStrings.t('daily_challenge'),
          36,
          31,
          24,
          family: 'DaysOne',
          color: Colors.white,
          lineHeight: .96,
        ),
        panel(
          36,
          100,
          AppLanguage.instance.isHindi ? 136 : 107,
          26,
          Colors.white,
          radius: 13,
        ),
        label(
          AppStrings.t('join_a_quiz'),
          45,
          104,
          15,
          family: 'DaysOne',
          lineHeight: 1.192,
        ),
        asset('1-2_imgVector1.svg', 205, 25, 99.716, 108),
        asset('1-2_imgVector2.svg', 203.8, 22.8, 100.115, 106.86),
        _question(
          '?',
          231,
          17,
          85,
          3.34,
          const Color(0xFF44ED77),
          const Color(0xFF306B42),
        ),
        _question(
          '?',
          208,
          45,
          48,
          -11.91,
          const Color(0xFFFADF14),
          const Color(0xFFDE9000),
        ),
        _question(
          '?',
          265,
          51,
          48,
          8.65,
          const Color(0xFFD24DFE),
          const Color(0xFF8400B1),
        ),
        for (final p in [
          const Offset(238, 36),
          const Offset(275, 41),
          const Offset(213, 51),
          const Offset(294, 62),
        ])
          at(p.dx, p.dy, 3, 6, const FigmaAsset('1-2_imgVector3.svg')),
      ],
    ),
  );

  Widget _question(
    String text,
    double x,
    double y,
    double size,
    double angle,
    Color top,
    Color bottom,
  ) => at(
    x,
    y,
    size * .53,
    size * 1.2,
    Transform.rotate(
      angle: angle * math.pi / 180,
      child: ShaderMask(
        blendMode: BlendMode.srcIn,
        shaderCallback: (bounds) => LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [top, bottom],
        ).createShader(bounds),
        child: Text(
          text,
          style: TextStyle(
            fontFamily: 'HappySchool',
            fontSize: size,
            height: 1.192,
            color: Colors.white,
          ),
        ),
      ),
    ),
  );
}
