import '../models/question_model.dart';
import 'api_client.dart';

/// Fetches quiz content (categories/quizzes/questions) from the Laravel
/// backend and submits results — the app has no local/CSV quiz data at
/// runtime; content is managed entirely through the admin panel.
class QuizApiService {
  QuizApiService._();
  static final QuizApiService instance = QuizApiService._();

  static const Map<String, String> _categorySlugs = {
    'math': 'mathematics-logic',
    'mathematics': 'mathematics-logic',
    'science': 'science-nature',
    'gk': 'general-knowledge',
  };

  List<Map<String, dynamic>>? _categoriesCache;

  Future<List<Map<String, dynamic>>> _categories() async {
    final cached = _categoriesCache;
    if (cached != null) return cached;
    final data = await ApiClient.instance.get('/categories');
    final categories = (data['categories'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    _categoriesCache = categories;
    return categories;
  }

  Future<Map<String, dynamic>?> _categoryFor(String categoryKey) async {
    final slug = _categorySlugs[categoryKey.toLowerCase()];
    if (slug == null) return null;
    final categories = await _categories();
    for (final category in categories) {
      if (category['slug'] == slug) return category;
    }
    return null;
  }

  QuizTopic _topicFromJson(Map<String, dynamic> json, String categoryKey) {
    final title = json['title'] as String? ?? '';
    final hash = title.hashCode.abs();
    return QuizTopic(
      name: title,
      category: categoryKey,
      questionCount: (json['questions_count'] as num?)?.toInt() ?? 0,
      playedCount: 180 + (hash % 350),
      progress: 0.2 + ((hash % 3) * 0.25),
      questions: const [],
      remoteQuizId: (json['id'] as num).toInt(),
    );
  }

  /// Fetches the quizzes in [categoryKey] for the given language.
  /// Throws [ApiException] on failure — callers should show a retry state.
  Future<List<QuizTopic>> getTopicsForCategory({
    required String categoryKey,
    required bool isHindi,
  }) async {
    final category = await _categoryFor(categoryKey);
    if (category == null) return [];

    final data = await ApiClient.instance.get(
      '/categories/${category['id']}/quizzes?language=${isHindi ? 'hi' : 'en'}',
    );
    final quizzes = (data['quizzes'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    return quizzes.map((q) => _topicFromJson(q, categoryKey)).toList();
  }

  /// A small mixed set pulled from each category, for the dashboard's
  /// "trending" list. Throws [ApiException] if any category fetch fails.
  Future<List<QuizTopic>> getTrendingTopics({required bool isHindi}) async {
    final results = await Future.wait([
      getTopicsForCategory(categoryKey: 'math', isHindi: isHindi),
      getTopicsForCategory(categoryKey: 'science', isHindi: isHindi),
      getTopicsForCategory(categoryKey: 'gk', isHindi: isHindi),
    ]);
    final math = results[0];
    final science = results[1];
    final gk = results[2];

    final list = <QuizTopic>[];
    if (math.isNotEmpty) list.add(math[0]);
    if (science.isNotEmpty) list.add(science[0]);
    if (gk.isNotEmpty) list.add(gk[0]);
    if (math.length > 1) {
      list.add(math[1]);
    } else if (gk.length > 1) {
      list.add(gk[1]);
    }
    return list;
  }

  /// Fetches the full question set (with options and correct answers) for
  /// [quizId]. Throws [ApiException] on failure.
  Future<List<Question>> fetchQuizQuestions(int quizId) async {
    final data = await ApiClient.instance.get('/quizzes/$quizId');
    final quiz = data['quiz'] as Map<String, dynamic>? ?? {};
    final questions = (quiz['questions'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    return questions.map(Question.fromRemoteJson).toList();
  }

  /// Submits final answers ({remote question id: selected remote option id})
  /// for authoritative server-side scoring. Throws [ApiException] on failure.
  Future<Map<String, dynamic>> submitQuiz({
    required int quizId,
    required Map<int, int> answers,
  }) async {
    final data = await ApiClient.instance.post('/quizzes/$quizId/submit', body: {
      'answers': answers.map((questionId, optionId) => MapEntry('$questionId', optionId)),
    });
    return data['result'] as Map<String, dynamic>? ?? {};
  }
}
