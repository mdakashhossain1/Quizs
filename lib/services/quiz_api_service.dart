import '../l10n/app_strings.dart';
import '../models/question_model.dart';
import '../models/quiz_ranking_model.dart';
import 'api_client.dart';

/// Fetches all quiz content from the Laravel backend.
/// No local/CSV data is used — all categories, quizzes, and questions
/// are managed through the admin panel.
class QuizApiService {
  QuizApiService._();
  static final QuizApiService instance = QuizApiService._();

  List<QuizCategory>? _categoriesCache;

  /// Fetches all active categories from the backend.
  Future<List<QuizCategory>> getCategories({bool forceRefresh = false}) async {
    if (!forceRefresh && _categoriesCache != null) return _categoriesCache!;
    try {
      final data = await ApiClient.instance.get('/categories');
      final rawList = data['categories'] ?? data['data'];
      final list = (rawList as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
      _categoriesCache = list.map(QuizCategory.fromJson).toList();
      return _categoriesCache!;
    } catch (_) {
      if (_categoriesCache != null) return _categoriesCache!;
      rethrow;
    }
  }

  /// Invalidates the category cache (call when the admin makes changes).
  void invalidateCache() => _categoriesCache = null;

  QuizTopic _topicFromJson(
    Map<String, dynamic> json,
    QuizCategory category,
  ) {
    final title = json['title'] as String? ?? '';
    // Roadmap §10-11: Played = unique users who completed this quiz;
    // progress = unique-user completion rate among those who started it.
    // Both come straight from the backend now — no more per-title hashing.
    final completionRate = (json['completion_rate'] as num?)?.toDouble() ?? 0;
    return QuizTopic(
      name: title,
      category: category.name,
      questionCount: (json['questions_count'] as num?)?.toInt() ?? 0,
      playedCount: (json['played_count'] as num?)?.toInt() ?? 0,
      progress: completionRate / 100,
      remoteQuizId: (json['id'] as num).toInt(),
      categoryColor: category.color,
      categorySlug: category.slug,
    );
  }

  /// Fetches quizzes for a specific category id.
  Future<List<QuizTopic>> getTopicsForCategoryId(int categoryId) async {
    final categories = await getCategories();
    final cat = categories.firstWhere(
      (c) => c.id == categoryId,
      orElse: () => const QuizCategory(
        id: 0, name: '', slug: '', color: '#6900C5', quizzesCount: 0,
      ),
    );
    // Bilingual quizzes always come back regardless of language; a legacy
    // single-language quiz only comes back for its own fixed language
    // (bilingual_question_management_prd.md §9).
    final data = await ApiClient.instance.get(
      '/categories/$categoryId/quizzes?language=${AppLanguage.instance.code}',
    );
    final quizzes = (data['quizzes'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    return quizzes.map((q) => _topicFromJson(q, cat)).toList();
  }

  /// Fetches quizzes for a category by slug. Used by legacy routes
  /// (e.g. /science, /mathematics) until they are migrated to id-based routing.
  Future<List<QuizTopic>> getTopicsForSlug(String slug) async {
    final categories = await getCategories();
    final cat = categories.firstWhere(
      (c) => c.slug == slug,
      orElse: () => categories.isNotEmpty ? categories.first : const QuizCategory(
        id: 0, name: '', slug: '', color: '#6900C5', quizzesCount: 0,
      ),
    );
    if (cat.id == 0) return [];
    return getTopicsForCategoryId(cat.id);
  }

  /// A mixed set of quizzes — one or two per category — for dashboard trending.
  Future<List<QuizTopic>> getTrendingTopics() async {
    final categories = await getCategories();
    if (categories.isEmpty) return [];

    final results = await Future.wait(
      categories.map((cat) => getTopicsForCategoryId(cat.id)),
    );

    final trending = <QuizTopic>[];
    for (final catTopics in results) {
      if (catTopics.isNotEmpty) trending.add(catTopics.first);
    }
    // Add one more from the first category if it has extras
    if (results.isNotEmpty && results[0].length > 1) {
      trending.add(results[0][1]);
    }
    return trending;
  }

  /// Fetches the full question set (with options and correct answers) for
  /// [quizId] in the app's current language — a bilingual quiz's questions
  /// come back pre-localized by the backend; a legacy single-language quiz
  /// ignores [lang] entirely (bilingual_question_management_prd.md §9).
  Future<List<Question>> fetchQuizQuestions(int quizId, {String? lang}) async {
    final data = await ApiClient.instance.get(
      '/quizzes/$quizId?lang=${lang ?? AppLanguage.instance.code}',
    );
    final quiz = data['quiz'] as Map<String, dynamic>? ?? {};
    final questions = (quiz['questions'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    return questions.map(Question.fromRemoteJson).toList();
  }

  /// Starts a tracked attempt for [quizId] and returns its backend attempt id.
  /// Recorded immediately so an abandoned quiz still shows up for analytics
  /// even if the user never reaches [submitAttempt].
  Future<int> startAttempt(int quizId) async {
    final data = await ApiClient.instance.post(
      '/quizzes/$quizId/start?lang=${AppLanguage.instance.code}',
    );
    final attempt = data['attempt'] as Map<String, dynamic>? ?? {};
    final id = (attempt['id'] as num?)?.toInt();
    if (id == null) throw Exception('Server did not return an attempt id.');
    return id;
  }

  /// Saves (or updates) the answer for one question of an in-progress
  /// attempt. Best-effort — callers should not block the UI on this beyond
  /// what's needed to move to the next question.
  Future<void> saveAnswer({
    required int attemptId,
    required int questionId,
    required int selectedOptionId,
  }) async {
    await ApiClient.instance.post('/attempts/$attemptId/answer', body: {
      'question_id': questionId,
      'selected_option_id': selectedOptionId,
    });
  }

  /// Finalizes [attemptId] from its already-saved answers and returns the
  /// server-authoritative result (score/accuracy/streak/quiz ranking).
  Future<Map<String, dynamic>> submitAttempt(int attemptId) async {
    final data = await ApiClient.instance.post('/attempts/$attemptId/submit');
    return data['result'] as Map<String, dynamic>? ?? {};
  }

  /// Refetches quiz-specific ranking without resubmitting — e.g. to show an
  /// up-to-date leaderboard if the result screen is revisited later.
  Future<QuizRanking> fetchQuizRanking(int quizId) async {
    final data = await ApiClient.instance.get('/quizzes/$quizId/ranking');
    final ranking = data['ranking'] as Map<String, dynamic>? ?? {};
    return QuizRanking.fromJson(ranking);
  }

  /// Builds a [QuizTopic] for a single quiz by id — used to deep-link a
  /// push notification's `quiz_details` destination straight into that
  /// quiz's question flow without hardcoding anything about it client-side.
  Future<QuizTopic> fetchQuizTopic(int quizId) async {
    final data = await ApiClient.instance.get('/quizzes/$quizId');
    final quiz = data['quiz'] as Map<String, dynamic>? ?? {};
    final category = quiz['category'] as Map<String, dynamic>? ?? {};
    final questions = quiz['questions'] as List<dynamic>? ?? [];
    final completionRate = (quiz['completion_rate'] as num?)?.toDouble() ?? 0;

    return QuizTopic(
      name: quiz['title'] as String? ?? '',
      category: category['name'] as String? ?? '',
      questionCount: questions.length,
      playedCount: (quiz['played_count'] as num?)?.toInt() ?? 0,
      progress: completionRate / 100,
      remoteQuizId: (quiz['id'] as num).toInt(),
      categoryColor: category['color'] as String?,
      categorySlug: category['slug'] as String?,
    );
  }
}
