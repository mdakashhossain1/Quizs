/// One row of a per-quiz leaderboard (roadmap "Quiz Result / Leaderboard
/// Page") — deliberately a separate type from any global-ranking model,
/// since quiz-specific and global ranking must never be conflated.
class QuizRankingEntry {
  const QuizRankingEntry({
    required this.rank,
    required this.userId,
    required this.name,
    this.avatar,
    required this.score,
    required this.correctAnswers,
    required this.wrongAnswers,
    required this.accuracy,
    this.timeTakenSeconds,
  });

  final int rank;
  final int userId;
  final String name;
  final String? avatar;
  final int score;
  final int correctAnswers;
  final int wrongAnswers;

  /// 0-100.
  final double accuracy;
  final int? timeTakenSeconds;

  factory QuizRankingEntry.fromJson(Map<String, dynamic> json) => QuizRankingEntry(
        rank: (json['rank'] as num?)?.toInt() ?? 0,
        userId: (json['user_id'] as num?)?.toInt() ?? 0,
        name: json['name'] as String? ?? '',
        avatar: json['avatar'] as String?,
        score: (json['score'] as num?)?.toInt() ?? 0,
        correctAnswers: (json['correct_answers'] as num?)?.toInt() ?? 0,
        wrongAnswers: (json['wrong_answers'] as num?)?.toInt() ?? 0,
        accuracy: (json['accuracy'] as num?)?.toDouble() ?? 0,
        timeTakenSeconds: (json['time_taken_seconds'] as num?)?.toInt(),
      );
}

/// Best-attempt-per-user leaderboard for one quiz, plus the current user's
/// own position (which may fall outside [top]).
class QuizRanking {
  const QuizRanking({required this.top, required this.yourRank, required this.totalParticipants});

  final List<QuizRankingEntry> top;

  /// 1-based; null if the current user hasn't completed this quiz.
  final int? yourRank;
  final int totalParticipants;

  static const empty = QuizRanking(top: [], yourRank: null, totalParticipants: 0);

  factory QuizRanking.fromJson(Map<String, dynamic> json) => QuizRanking(
        top: (json['top'] as List<dynamic>? ?? [])
            .cast<Map<String, dynamic>>()
            .map(QuizRankingEntry.fromJson)
            .toList(),
        yourRank: (json['your_rank'] as num?)?.toInt(),
        totalParticipants: (json['total_participants'] as num?)?.toInt() ?? 0,
      );
}
