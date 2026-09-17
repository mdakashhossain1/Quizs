import 'api_client.dart';

/// One row of the global/Achievement-page ranking (leaderboard-achievement
/// roadmap Part B) — deliberately a different shape from
/// [QuizRankingEntry]: this is level/xp/accuracy-driven and global, never a
/// single quiz's own leaderboard.
class AchievementRankingEntry {
  const AchievementRankingEntry({
    required this.rank,
    required this.userId,
    required this.name,
    this.avatar,
    required this.level,
    required this.xp,
    required this.accuracy,
    required this.quizPlayed,
  });

  /// 1-based position in the full ranking, derived from this entry's index
  /// in the backend's already-ordered `top` list.
  final int rank;
  final int userId;
  final String name;
  final String? avatar;
  final int level;
  final int xp;

  /// 0-100.
  final double accuracy;
  final int quizPlayed;

  factory AchievementRankingEntry.fromJson(Map<String, dynamic> json, int rank) =>
      AchievementRankingEntry(
        rank: rank,
        userId: (json['user_id'] as num?)?.toInt() ?? 0,
        name: json['name'] as String? ?? '',
        avatar: json['avatar'] as String?,
        level: (json['level'] as num?)?.toInt() ?? 1,
        xp: (json['xp'] as num?)?.toInt() ?? 0,
        accuracy: (json['accuracy'] as num?)?.toDouble() ?? 0,
        quizPlayed: (json['quiz_played'] as num?)?.toInt() ?? 0,
      );
}

/// The Achievement page's single consolidated fetch: the current user's own
/// profile summary (same source as the Profile screen), the site-wide
/// Active Users / Active This Month counts, and the global ranking — kept
/// entirely separate from per-quiz ranking (roadmap Part C).
class AchievementSummary {
  const AchievementSummary({
    required this.name,
    this.avatar,
    required this.level,
    required this.accuracy,
    required this.todayProgressPercentage,
    required this.activeUsers,
    required this.activeThisMonth,
    required this.top,
    required this.yourRank,
    required this.totalEligibleUsers,
  });

  final String name;
  final String? avatar;
  final int level;

  /// 0-100.
  final double accuracy;

  /// 0-100, already capped server-side.
  final double todayProgressPercentage;

  final int activeUsers;
  final int activeThisMonth;

  final List<AchievementRankingEntry> top;

  /// 1-based; null only if the current user is somehow not in the eligible pool.
  final int? yourRank;
  final int totalEligibleUsers;

  /// Shown while the real summary is loading, or if the request fails —
  /// never presented as if it were real backend data.
  static const empty = AchievementSummary(
    name: '',
    avatar: null,
    level: 1,
    accuracy: 0,
    todayProgressPercentage: 0,
    activeUsers: 0,
    activeThisMonth: 0,
    top: [],
    yourRank: null,
    totalEligibleUsers: 0,
  );

  factory AchievementSummary.fromJson(Map<String, dynamic> json) {
    final profile = json['profile'] as Map<String, dynamic>? ?? {};
    final level = profile['level'] as Map<String, dynamic>? ?? {};
    final today = profile['today_target'] as Map<String, dynamic>? ?? {};
    final activeUsers = json['active_users'] as Map<String, dynamic>? ?? {};
    final ranking = json['ranking'] as Map<String, dynamic>? ?? {};
    final topList = (ranking['top'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();

    return AchievementSummary(
      name: profile['name'] as String? ?? '',
      avatar: profile['avatar'] as String?,
      level: (level['level'] as num?)?.toInt() ?? 1,
      accuracy: (profile['accuracy'] as num?)?.toDouble() ?? 0,
      todayProgressPercentage: (today['progress_percentage'] as num?)?.toDouble() ?? 0,
      activeUsers: (activeUsers['active_users'] as num?)?.toInt() ?? 0,
      activeThisMonth: (activeUsers['active_this_month'] as num?)?.toInt() ?? 0,
      top: [
        for (var i = 0; i < topList.length; i++)
          AchievementRankingEntry.fromJson(topList[i], i + 1),
      ],
      yourRank: (ranking['your_rank'] as num?)?.toInt(),
      totalEligibleUsers: (ranking['total_eligible_users'] as num?)?.toInt() ?? 0,
    );
  }
}

class AchievementService {
  AchievementService._();
  static final AchievementService instance = AchievementService._();

  /// Throws [ApiException] on failure — callers should fall back to
  /// [AchievementSummary.empty] rather than show stale/fabricated numbers.
  Future<AchievementSummary> fetch() async {
    final data = await ApiClient.instance.get('/achievement');
    final achievement = data['achievement'] as Map<String, dynamic>? ?? {};
    return AchievementSummary.fromJson(achievement);
  }
}
