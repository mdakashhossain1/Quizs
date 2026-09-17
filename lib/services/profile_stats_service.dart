import 'api_client.dart';

/// Backend-computed profile statistics (roadmap §8, §9). These are three
/// distinct concepts that must never be conflated: [todayProgressPercentage]
/// is today's daily-target completion, [level]/[xp] is accumulated
/// progression from completed daily targets, and [accuracy] is the
/// correct-answer rate from real question-attempt data.
class ProfileStats {
  const ProfileStats({
    required this.level,
    required this.xp,
    required this.todayCompleted,
    required this.todayTarget,
    required this.todayProgressPercentage,
    required this.accuracy,
    required this.quizPlayed,
    required this.right,
    required this.wrong,
    required this.thisMonth,
    required this.rank,
  });

  final int level;
  final int xp;
  final int todayCompleted;
  final int todayTarget;

  /// 0-100, already capped server-side.
  final double todayProgressPercentage;

  /// 0-100.
  final double accuracy;

  final int quizPlayed;
  final int right;
  final int wrong;
  final int thisMonth;

  /// 1-based leaderboard position (roadmap §12) — never invent this on the
  /// client, always the backend's RankingService value.
  final int rank;

  /// Shown while the real stats are loading, or if the request fails —
  /// never presented as if it were real backend data.
  static const empty = ProfileStats(
    level: 1,
    xp: 0,
    todayCompleted: 0,
    todayTarget: 0,
    todayProgressPercentage: 0,
    accuracy: 0,
    quizPlayed: 0,
    right: 0,
    wrong: 0,
    thisMonth: 0,
    rank: 0,
  );

  factory ProfileStats.fromJson(Map<String, dynamic> json) {
    final level = json['level'] as Map<String, dynamic>? ?? {};
    final today = json['today_target'] as Map<String, dynamic>? ?? {};

    return ProfileStats(
      level: (level['level'] as num?)?.toInt() ?? 1,
      xp: (level['xp'] as num?)?.toInt() ?? 0,
      todayCompleted: (today['completed_quizzes'] as num?)?.toInt() ?? 0,
      todayTarget: (today['effective_target'] as num?)?.toInt() ?? 0,
      todayProgressPercentage: (today['progress_percentage'] as num?)?.toDouble() ?? 0,
      accuracy: (json['accuracy'] as num?)?.toDouble() ?? 0,
      quizPlayed: (json['quiz_played'] as num?)?.toInt() ?? 0,
      right: (json['right'] as num?)?.toInt() ?? 0,
      wrong: (json['wrong'] as num?)?.toInt() ?? 0,
      thisMonth: (json['this_month'] as num?)?.toInt() ?? 0,
      rank: (json['rank'] as num?)?.toInt() ?? 0,
    );
  }
}

class ProfileStatsService {
  ProfileStatsService._();
  static final ProfileStatsService instance = ProfileStatsService._();

  /// Throws [ApiException] on failure — callers should fall back to
  /// [ProfileStats.empty] rather than show stale/fabricated numbers.
  Future<ProfileStats> fetch() async {
    final data = await ApiClient.instance.get('/profile/stats');
    final stats = data['stats'] as Map<String, dynamic>? ?? {};
    return ProfileStats.fromJson(stats);
  }
}
