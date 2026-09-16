import 'api_client.dart';

class LeaderboardEntry {
  const LeaderboardEntry({
    required this.name,
    required this.score,
    required this.streak,
  });

  final String name;
  final int score;
  final int streak;

  factory LeaderboardEntry.fromJson(Map<String, dynamic> json) => LeaderboardEntry(
        name: json['name'] as String? ?? '',
        score: (json['score'] as num?)?.toInt() ?? 0,
        streak: (json['streak'] as num?)?.toInt() ?? 0,
      );
}

class LeaderboardService {
  LeaderboardService._();
  static final LeaderboardService instance = LeaderboardService._();

  /// Fetches the real top scorers from the Laravel API. Throws [ApiException]
  /// on failure (e.g. server unreachable) — callers should degrade gracefully.
  Future<List<LeaderboardEntry>> fetchTop() async {
    final data = await ApiClient.instance.get('/leaderboard');
    final list = data['leaderboard'] as List<dynamic>? ?? [];
    return list
        .whereType<Map<String, dynamic>>()
        .map(LeaderboardEntry.fromJson)
        .toList();
  }
}
