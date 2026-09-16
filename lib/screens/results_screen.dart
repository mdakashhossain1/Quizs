import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/leaderboard_service.dart';
import '../widgets/design_widgets.dart';

class QuizResultArgs {
  const QuizResultArgs({
    this.totalSolved = 10,
    this.rightCount = 8,
    this.wrongCount = 2,
    this.scorePercentage = 80,
  });

  final int totalSolved;
  final int rightCount;
  final int wrongCount;
  final int scorePercentage;
}

class ResultsScreen extends StatefulWidget {
  const ResultsScreen({super.key, this.resultArgs});
  final QuizResultArgs? resultArgs;

  @override
  State<ResultsScreen> createState() => _ResultsScreenState();
}

class _ResultsScreenState extends State<ResultsScreen> {
  List<LeaderboardEntry> _leaderboard = [];

  @override
  void initState() {
    super.initState();
    _loadLeaderboard();
  }

  Future<void> _loadLeaderboard() async {
    try {
      final leaderboard = await LeaderboardService.instance.fetchTop();
      if (mounted) setState(() => _leaderboard = leaderboard);
    } catch (_) {
      // No live leaderboard data available; podium stays blank rather than fake.
    }
  }

  LeaderboardEntry? _entryAt(int index) =>
      index < _leaderboard.length ? _leaderboard[index] : null;

  @override
  Widget build(BuildContext context) {
    final res = widget.resultArgs ?? const QuizResultArgs();

    return DesignCanvas(
      adAfter: 805,
      topColor: const Color(0xFF31005C),
      darkBanner: false,
      children: [
        const Positioned.fill(child: QuestionBackground(results: true)),
        label(
          'Quizs',
          159,
          26,
          32,
          family: 'Quizlo',
          color: Colors.white,
          lineHeight: 1.44,
        ),
        backButton(context, compact: true),
        asset('12-310_imgHome1.png', 363, 37, 22, 22, color: Colors.white),
        at(
          350,
          24,
          48,
          48,
          DesignAction(
            label: AppStrings.t('return_home'),
            onTap: () => goHome(context),
          ),
        ),
        panel(35, 143, 341, 186, Colors.white, radius: 22),
        at(
          35,
          144,
          341,
          181,
          ClipRRect(
            borderRadius: BorderRadius.circular(22),
            child: Stack(
              children: [
                at(
                  97,
                  -34.034,
                  147,
                  113.705,
                  ClipOval(
                    child: Stack(
                      children: [
                        at(
                          -19.768,
                          -27.001,
                          178.537,
                          178.537,
                          const Opacity(
                            opacity: .16,
                            child: FigmaAsset(
                              '12-310_imgRadialLines3.png',
                              color: Color(0x24FFC404),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        asset('12-310_imgTrophy31.png', 145, 82, 122, 122),
        label(
          AppStrings.t('congratulations'),
          0,
          204,
          20,
          width: 412,
          align: TextAlign.center,
          color: const Color(0xFF6703BF),
          weight: FontWeight.w800,
        ),
        at(
          0,
          239,
          412,
          18,
          const Center(child: ScoreCaption()),
        ),
        panel(49, 273, 314, 1, const Color(0xFFD9D9D9)),
        for (var i = 0; i < 3; i++) ...[
          label(
            ['${res.totalSolved}', '${res.rightCount}', '${res.wrongCount}'][i],
            [95.0, 198.0, 302.0][i],
            279,
            24,
            color: [
              QuizColors.purple,
              QuizColors.green,
              const Color(0xFFFF0000),
            ][i],
            weight: FontWeight.w700,
            lineHeight: 1.25,
          ),
          label(
            [
              AppStrings.t('question_solved'),
              AppStrings.t('right_stat'),
              AppStrings.t('wrong_stat'),
            ][i],
            [40.0, 180.0, 275.0][i],
            305,
            11,
            width: [120.0, 60.0, 60.0][i],
            align: TextAlign.center,
            color: const Color(0x70000000),
            weight: FontWeight.w400,
            lineHeight: 1.25,
          ),
        ],
        asset('12-310_imgVector15.svg', 237.4, 502, 77.2, 12),
        asset('12-310_imgVector17.svg', 87.4, 488, 77.2, 11),
        asset('12-310_imgVector16.svg', 237, 513, 78, 167),
        asset('12-310_imgVector16.svg', 87, 498, 78, 167),
        asset('12-310_imgVector13.svg', 162.5, 454, 77, 10),
        at(
          162,
          463,
          78,
          167,
          const DecoratedBox(
            decoration: BoxDecoration(
              boxShadow: [
                BoxShadow(
                  color: Color(0x40000000),
                  offset: Offset(-4, 5),
                  blurRadius: 4,
                ),
              ],
            ),
          ),
        ),
        asset('12-310_imgVector14.svg', 154, 463, 86, 176),
        asset('12-310_imgVector19.svg', 144.5, 335, 113, 127.5),
        asset('12-310_imgProfile3.png', 168, 358, 65, 65),
        asset('12-310_imgProfile3.png', 98, 401, 51, 51),
        asset('12-310_imgProfile3.png', 261, 436, 31, 31),
        at(
          159,
          314,
          57.5,
          57.5,
          Transform.rotate(
            angle: -12.9 * math.pi / 180,
            child: const Center(
              child: SizedBox(
                width: 48,
                height: 48,
                child: FigmaAsset('12-310_imgKing1.png'),
              ),
            ),
          ),
        ),
        label(
          _entryAt(1)?.name ?? '',
          91,
          464,
          10,
          weight: FontWeight.w700,
          color: Colors.white,
          lineHeight: 1.3,
        ),
        label(
          _entryAt(0)?.name ?? '',
          168,
          434,
          10,
          weight: FontWeight.w700,
          color: Colors.white,
          lineHeight: 1.3,
        ),
        label(
          _entryAt(2)?.name ?? '',
          244,
          481,
          10,
          weight: FontWeight.w700,
          color: Colors.white,
          lineHeight: 1.3,
        ),
        panel(0, 630, 412, 352, Colors.white, radius: 27),
        for (var i = 0; i < 2; i++) ...[
          panel(
            0,
            659 + i * 73,
            412,
            73,
            Colors.white,
            border: const Color(0xFFE8E8E8),
            borderWidth: .5,
          ),
          label(
            i == 0 ? '3' : '4',
            38,
            684 + i * 73,
            24,
            weight: FontWeight.w700,
            color: i == 0 ? const Color(0xFF383838) : Colors.black,
            lineHeight: 1.25,
          ),
          label(
            i == 0 ? 'rd' : 'th',
            i == 0 ? 51 : 52,
            685 + i * 73,
            12,
            weight: FontWeight.w700,
            lineHeight: 1.25,
          ),
          asset('12-310_imgProfile3.png', 71, 680 + i * 73, 39, 39),
          label(
            _entryAt(i + 3)?.name ?? '',
            126,
            684 + i * 73,
            12,
            weight: FontWeight.w700,
            color: const Color(0xFF383838),
            lineHeight: 1.25,
          ),
          label(
            _entryAt(i + 3) != null ? '${_entryAt(i + 3)!.streak} day streak' : '',
            126,
            699 + i * 73,
            8,
            color: const Color(0x99000000),
            lineHeight: 1.5,
          ),
          label(
            _entryAt(i + 3) != null ? '${_entryAt(i + 3)!.score} pts' : '',
            342,
            689 + i * 73,
            16,
            weight: FontWeight.w700,
            color: i == 0 ? const Color(0xFF383838) : Colors.black,
            lineHeight: 1.25,
          ),
        ],
      ],
    );
  }
}

class ScoreCaption extends StatelessWidget {
  const ScoreCaption({super.key, this.size = 12});
  final double size;

  @override
  Widget build(BuildContext context) => Text.rich(
        TextSpan(
          children: [
            TextSpan(text: AppStrings.t('score_prefix')),
            const TextSpan(
              text: '100+',
              style: TextStyle(
                color: Color(0xFF00EB4E),
                fontWeight: FontWeight.w500,
              ),
            ),
            TextSpan(text: AppStrings.t('score_suffix')),
          ],
        ),
        style: TextStyle(
          fontFamily: 'Poppins',
          fontSize: size,
          height: 1.5,
          fontWeight: FontWeight.w300,
          color: Colors.black,
        ),
      );
}
