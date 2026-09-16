class Question {
  const Question({
    required this.id,
    required this.language,
    required this.category,
    required this.subtopic,
    required this.difficulty,
    required this.question,
    required this.optionA,
    required this.optionB,
    required this.optionC,
    required this.optionD,
    required this.correctOption,
    required this.correctAnswer,
    required this.meaning,
    required this.explanation,
    this.remoteId,
    this.remoteOptionIds,
  });

  final String id;
  final String language;
  final String category;
  final String subtopic;
  final String difficulty;
  final String question;
  final String optionA;
  final String optionB;
  final String optionC;
  final String optionD;
  final String correctOption; // 'A', 'B', 'C', 'D'
  final String correctAnswer;
  final String meaning;
  final String explanation;

  /// Backend question id, set only when this [Question] was loaded from the
  /// Laravel API rather than a local CSV row. Used together with
  /// [remoteOptionIds] to submit answers via `/api/quizzes/{id}/submit`.
  final int? remoteId;

  /// Backend option ids in the same A/B/C/D order as [options], set only
  /// when this [Question] came from the API.
  final List<int>? remoteOptionIds;

  List<String> get options => [optionA, optionB, optionC, optionD];

  int get correctOptionIndex {
    switch (correctOption.toUpperCase().trim()) {
      case 'A':
        return 0;
      case 'B':
        return 1;
      case 'C':
        return 2;
      case 'D':
        return 3;
      default:
        return 0;
    }
  }

  factory Question.fromCsvRow(List<String> row) {
    String get(int idx) => idx < row.length ? row[idx] : '';
    return Question(
      id: get(0),
      language: get(1),
      category: get(2),
      subtopic: get(3),
      difficulty: get(4),
      question: get(5),
      optionA: get(6),
      optionB: get(7),
      optionC: get(8),
      optionD: get(9),
      correctOption: get(10),
      correctAnswer: get(11),
      meaning: get(12),
      explanation: get(13),
    );
  }

  /// Builds a [Question] from a Laravel quiz-detail question payload
  /// (`question_text`/`meaning`/`explanation` + an `options` array of
  /// `{id, option_text, is_correct}`), preserving backend ids for submission.
  factory Question.fromRemoteJson(Map<String, dynamic> json) {
    final options = (json['options'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    final optionTexts = List<String>.generate(
      4,
      (i) => i < options.length ? (options[i]['option_text'] as String? ?? '') : '',
    );
    final correctIndex = options.indexWhere((o) => o['is_correct'] == true);
    const letters = ['A', 'B', 'C', 'D'];

    return Question(
      id: '${json['id']}',
      language: '',
      category: '',
      subtopic: '',
      difficulty: '',
      question: json['question_text'] as String? ?? '',
      optionA: optionTexts[0],
      optionB: optionTexts[1],
      optionC: optionTexts[2],
      optionD: optionTexts[3],
      correctOption: correctIndex >= 0 ? letters[correctIndex] : 'A',
      correctAnswer: correctIndex >= 0 ? optionTexts[correctIndex] : '',
      meaning: json['meaning'] as String? ?? '',
      explanation: json['explanation'] as String? ?? '',
      remoteId: (json['id'] as num).toInt(),
      remoteOptionIds: options.map((o) => (o['id'] as num).toInt()).toList(),
    );
  }
}

class QuizTopic {
  const QuizTopic({
    required this.name,
    required this.category,
    required this.questionCount,
    required this.playedCount,
    required this.progress,
    required this.questions,
    this.remoteQuizId,
  });

  final String name;
  final String category;
  final int questionCount;
  final int playedCount;
  final double progress;
  final List<Question> questions;

  /// Backend quiz id, set when this topic was loaded from the Laravel API.
  /// [QuestionScreen] uses it to lazily fetch the full question set.
  final int? remoteQuizId;

  String get cleanName => name.trim();
}

