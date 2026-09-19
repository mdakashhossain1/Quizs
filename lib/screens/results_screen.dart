import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../models/quiz_ranking_model.dart';
import '../services/quiz_api_service.dart';
import '../services/sound_service.dart';
import '../widgets/design_widgets.dart';

/// Result-message copy per [QuizResultArgs.performanceState] — the backend
/// only decides WHICH tier applies (config('quiz.performance_thresholds')),
/// the frontend owns the actual wording/color (roadmap "Dynamic Result
/// Message"). Change here to retune copy without touching the API.
class _PerformanceMessage {
  const _PerformanceMessage(this.headingKey, this.color);
  final String headingKey;
  final Color color;
}

const _performanceMessages = {
  'excellent': _PerformanceMessage('congratulations', Color(0xFF6703BF)),
  'good': _PerformanceMessage('result_good', Color(0xFF10BA65)),
  'average': _PerformanceMessage('result_average', Color(0xFFFF9800)),
  'low': _PerformanceMessage('result_low', Color(0xFFE53935)),
};

class QuizResultArgs {
  const QuizResultArgs({
    this.totalSolved = 10,
    this.rightCount = 8,
    this.wrongCount = 2,
    this.scorePercentage = 80,
    this.accuracy,
    this.timeTakenSeconds,
    this.performanceState = 'good',
    this.quizRanking = QuizRanking.empty,
  });

  final int totalSolved;
  final int rightCount;
  final int wrongCount;
  final int scorePercentage;
  final double? accuracy;
  final int? timeTakenSeconds;

  /// 'excellent' | 'good' | 'average' | 'low' — see [_performanceMessages].
  final String performanceState;

  /// This quiz's own leaderboard (best attempt per user) — never the global
  /// Achievement-page ranking (roadmap Part C: the two must stay separate).
  final QuizRanking quizRanking;

  /// Parses the same `result` shape [QuizApiService.submitAttempt] and
  /// [QuizApiService.fetchGlobalResult] both return (total/correct/wrong,
  /// accuracy, performance_state, quiz_ranking) — this is what makes the one
  /// results/podium screen able to show either a single quiz's outcome or
  /// the site-wide totals+leaderboard.
  factory QuizResultArgs.fromResultJson(Map<String, dynamic> json) {
    final total = (json['total_questions'] as num?)?.toInt() ?? 0;
    final right = (json['correct_answers'] as num?)?.toInt() ?? 0;
    final wrong = (json['wrong_answers'] as num?)?.toInt() ?? (total - right);
    final rankingJson = json['quiz_ranking'] as Map<String, dynamic>?;

    return QuizResultArgs(
      totalSolved: total,
      rightCount: right,
      wrongCount: wrong,
      scorePercentage: (json['percentage'] as num?)?.round() ?? 0,
      accuracy: (json['accuracy'] as num?)?.toDouble(),
      timeTakenSeconds: (json['time_taken_seconds'] as num?)?.toInt(),
      performanceState: json['performance_state'] as String? ?? 'good',
      quizRanking: rankingJson != null ? QuizRanking.fromJson(rankingJson) : QuizRanking.empty,
    );
  }
}

/// Shows either a single quiz's just-finished outcome (when [resultArgs] is
/// given) or, when opened with none — e.g. from Home's "Leaderboard" card —
/// fetches and shows the user's site-wide totals and the global leaderboard
/// on this same podium UI (see [QuizResultArgs.fromResultJson]).
class ResultsScreen extends StatefulWidget {
  const ResultsScreen({super.key, this.resultArgs});
  final QuizResultArgs? resultArgs;

  @override
  State<ResultsScreen> createState() => _ResultsScreenState();
}

class _ResultsScreenState extends State<ResultsScreen> {
  QuizResultArgs? _globalArgs;
  bool _loading = false;
  bool _loadFailed = false;

  @override
  void initState() {
    super.initState();
    if (widget.resultArgs == null) _loadGlobal();
  }

  Future<void> _loadGlobal() async {
    setState(() {
      _loading = true;
      _loadFailed = false;
    });
    try {
      final json = await QuizApiService.instance.fetchGlobalResult();
      if (!mounted) return;
      setState(() {
        _globalArgs = QuizResultArgs.fromResultJson(json);
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loadFailed = true;
        _loading = false;
      });
    }
  }

  /// A leaderboard entry's real profile photo when they have one, else the
  /// same generic placeholder graphic used before — every rank used to show
  /// the placeholder unconditionally, even for users with an uploaded avatar.
  static Widget _rankAvatar(QuizRankingEntry? entry, double x, double y, double size) {
    final avatarUrl = entry?.avatar;
    return at(
      x,
      y,
      size,
      size,
      ClipOval(
        child: (avatarUrl != null && avatarUrl.isNotEmpty)
            ? Image.network(
                avatarUrl,
                width: size,
                height: size,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => const FigmaAsset('12-310_imgProfile3.png'),
              )
            : const FigmaAsset('12-310_imgProfile3.png'),
      ),
    );
  }

  static String _ordinalSuffix(int rank) {
    if (rank % 100 >= 11 && rank % 100 <= 13) return 'th';
    switch (rank % 10) {
      case 1: return 'st';
      case 2: return 'nd';
      case 3: return 'rd';
      default: return 'th';
    }
  }

  @override
  Widget build(BuildContext context) {
    if (widget.resultArgs == null) {
      if (_loading) return const _ResultsLoading();
      if (_loadFailed || _globalArgs == null) return _ResultsError(onRetry: _loadGlobal);
    }
    final res = widget.resultArgs ?? _globalArgs!;
    final ranking = res.quizRanking;
    final message = _performanceMessages[res.performanceState] ?? _performanceMessages['good']!;
    if (res.performanceState == 'excellent' || res.performanceState == 'good') {
      WidgetsBinding.instance.addPostFrameCallback((_) => SoundService.instance.playVictory());
    }
    QuizRankingEntry? entryAt(int index) =>
        index < ranking.top.length ? ranking.top[index] : null;

    return DesignCanvas(
      adAfter: 840,
      topColor: const Color(0xFF31005C),
      darkBanner: false,
      fixedBackground: const QuestionBackground(results: true),
      children: [
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
          AppStrings.t(message.headingKey),
          0,
          204,
          20,
          width: 412,
          align: TextAlign.center,
          color: message.color,
          weight: FontWeight.w800,
        ),
        at(
          0,
          239,
          412,
          18,
          Center(
            child: ScoreCaption(
              accuracy: res.accuracy,
              timeTakenSeconds: res.timeTakenSeconds,
            ),
          ),
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
        _rankAvatar(entryAt(0), 168, 358, 65),
        _rankAvatar(entryAt(1), 98, 401, 51),
        _rankAvatar(entryAt(2), 261, 436, 31),
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
          entryAt(1)?.name ?? '',
          91,
          464,
          10,
          weight: FontWeight.w700,
          color: Colors.white,
          lineHeight: 1.3,
        ),
        label(
          entryAt(0)?.name ?? '',
          168,
          434,
          10,
          weight: FontWeight.w700,
          color: Colors.white,
          lineHeight: 1.3,
        ),
        label(
          entryAt(2)?.name ?? '',
          244,
          481,
          10,
          weight: FontWeight.w700,
          color: Colors.white,
          lineHeight: 1.3,
        ),
        panel(0, 630, 412, 352, Colors.white, radius: 27),
        // Only render a 4th/5th-place row when the leaderboard actually has
        // someone at that rank — a quiz with few players used to still show
        // two empty "3rd"/"4th" rows (hardcoded labels, blank name/score).
        for (var i = 0; i < 2; i++)
          if (entryAt(i + 3) != null) ...[
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
              '${entryAt(i + 3)!.rank}',
              38,
              684 + i * 73,
              24,
              weight: FontWeight.w700,
              color: i == 0 ? const Color(0xFF383838) : Colors.black,
              lineHeight: 1.25,
            ),
            label(
              _ordinalSuffix(entryAt(i + 3)!.rank),
              i == 0 ? 51 : 52,
              685 + i * 73,
              12,
              weight: FontWeight.w700,
              lineHeight: 1.25,
            ),
            _rankAvatar(entryAt(i + 3), 71, 680 + i * 73, 39),
            label(
              entryAt(i + 3)!.name,
              126,
              684 + i * 73,
              12,
              weight: FontWeight.w700,
              color: const Color(0xFF383838),
              lineHeight: 1.25,
            ),
            label(
              '${entryAt(i + 3)!.accuracy.round()}% accuracy',
              126,
              699 + i * 73,
              8,
              color: const Color(0x99000000),
              lineHeight: 1.5,
            ),
            label(
              '${entryAt(i + 3)!.score} pts',
              342,
              689 + i * 73,
              16,
              weight: FontWeight.w700,
              color: i == 0 ? const Color(0xFF383838) : Colors.black,
              lineHeight: 1.25,
            ),
          ],
        if (ranking.yourRank != null)
          label(
            'Your Rank: #${ranking.yourRank} of ${ranking.totalParticipants}',
            0,
            812,
            13,
            width: 412,
            align: TextAlign.center,
            color: const Color(0xFF757575),
            weight: FontWeight.w600,
          ),
      ],
    );
  }
}

/// Shown while [ResultsScreen] fetches the site-wide leaderboard (only
/// reached with no [ResultsScreen.resultArgs] — a just-finished quiz's own
/// result never needs to wait on a request here).
class _ResultsLoading extends StatelessWidget {
  const _ResultsLoading();

  @override
  Widget build(BuildContext context) => DesignCanvas(
        adAfter: 400,
        topColor: const Color(0xFF31005C),
        darkBanner: false,
        fixedBackground: const QuestionBackground(results: true),
        children: [
          backButton(context, compact: true),
          at(
            0,
            260,
            412,
            120,
            const Center(child: CircularProgressIndicator(color: Colors.white)),
          ),
        ],
      );
}

/// Shown if the site-wide leaderboard fetch fails — never a fabricated
/// scoreboard, per [ResultsScreen]'s doc comment.
class _ResultsError extends StatelessWidget {
  const _ResultsError({required this.onRetry});
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => DesignCanvas(
        adAfter: 400,
        topColor: const Color(0xFF31005C),
        darkBanner: false,
        fixedBackground: const QuestionBackground(results: true),
        children: [
          backButton(context, compact: true),
          at(
            26,
            240,
            360,
            160,
            Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text(
                    'Could not load the leaderboard.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontFamily: 'Poppins', fontSize: 14, color: Colors.white),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: onRetry,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Retry', style: TextStyle(color: QuizColors.purple)),
                  ),
                ],
              ),
            ),
          ),
        ],
      );
}

/// Roadmap "Dynamic Result Summary": shows the attempt's real accuracy and
/// time taken instead of a fixed "You have scored 100+ Points" caption that
/// never reflected the actual result.
class ScoreCaption extends StatelessWidget {
  const ScoreCaption({super.key, this.size = 12, this.accuracy, this.timeTakenSeconds});

  final double size;
  final double? accuracy;
  final int? timeTakenSeconds;

  String _formatDuration(int seconds) {
    final minutes = seconds ~/ 60;
    final secs = seconds % 60;
    return minutes > 0 ? '${minutes}m ${secs}s' : '${secs}s';
  }

  @override
  Widget build(BuildContext context) => Text.rich(
        TextSpan(
          children: [
            TextSpan(text: AppStrings.t('result_accuracy_label')),
            TextSpan(
              text: accuracy != null ? '${accuracy!.toStringAsFixed(1)}%' : '—',
              style: const TextStyle(
                color: Color(0xFF00EB4E),
                fontWeight: FontWeight.w500,
              ),
            ),
            if (timeTakenSeconds != null)
              TextSpan(text: '${AppStrings.t('result_time_label')}${_formatDuration(timeTakenSeconds!)}'),
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
