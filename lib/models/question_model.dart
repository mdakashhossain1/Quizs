import '../l10n/app_strings.dart';

/// A single quiz question loaded from the Laravel backend.
class Question {
  const Question({
    required this.id,
    required this.question,
    required this.optionA,
    required this.optionB,
    required this.optionC,
    required this.optionD,
    required this.correctOption,
    required this.correctAnswer,
    this.meaning = '',
    this.explanation = '',
    this.remoteId,
    this.remoteOptionIds,
  });

  final String id;
  final String question;
  final String optionA;
  final String optionB;
  final String optionC;
  final String optionD;

  /// 'A', 'B', 'C', or 'D'
  final String correctOption;
  final String correctAnswer;
  final String meaning;
  final String explanation;

  /// Backend question id — used to submit answers via `/api/quizzes/{id}/submit`.
  final int? remoteId;

  /// Backend option ids in A/B/C/D order — used alongside [remoteId] for submission.
  final List<int>? remoteOptionIds;

  List<String> get options => [optionA, optionB, optionC, optionD];

  int get correctOptionIndex {
    switch (correctOption.toUpperCase().trim()) {
      case 'A': return 0;
      case 'B': return 1;
      case 'C': return 2;
      case 'D': return 3;
      default:  return 0;
    }
  }

  /// Builds a [Question] from a Laravel quiz-detail question payload.
  factory Question.fromRemoteJson(Map<String, dynamic> json) {
    final opts = (json['options'] as List<dynamic>? ?? [])
        .cast<Map<String, dynamic>>();
    const letters = ['A', 'B', 'C', 'D'];
    List<String> texts;
    int correctIdx = -1;

    if (opts.isNotEmpty) {
      texts = List<String>.generate(
        4,
        (i) => i < opts.length ? (opts[i]['option_text'] as String? ?? '') : '',
      );
      correctIdx = opts.indexWhere((o) => o['is_correct'] == true || o['is_correct'] == 1);
    } else {
      texts = [
        json['option_a'] as String? ?? '',
        json['option_b'] as String? ?? '',
        json['option_c'] as String? ?? '',
        json['option_d'] as String? ?? '',
      ];
      final corr = (json['correct_option'] as String? ?? 'A').toUpperCase().trim();
      correctIdx = letters.indexOf(corr);
      if (correctIdx < 0) correctIdx = 0;
    }

    final qText = (json['question_text'] ?? json['question']) as String? ?? '';
    final idNum = json['id'] is num ? (json['id'] as num).toInt() : int.tryParse('${json['id']}');

    return Question(
      id: '${json['id']}',
      question: qText,
      optionA: texts[0],
      optionB: texts[1],
      optionC: texts[2],
      optionD: texts[3],
      correctOption: correctIdx >= 0 ? letters[correctIdx] : 'A',
      correctAnswer: correctIdx >= 0 ? texts[correctIdx] : '',
      meaning: json['meaning'] as String? ?? '',
      explanation: json['explanation'] as String? ?? '',
      remoteId: idNum,
      remoteOptionIds: opts.map((o) => (o['id'] as num).toInt()).toList(),
    );
  }
}

/// A quiz loaded from the Laravel backend.
class QuizTopic {
  const QuizTopic({
    required this.name,
    required this.category,
    required this.questionCount,
    required this.playedCount,
    required this.progress,
    this.remoteQuizId,
    this.categoryColor,
    this.categorySlug,
  });

  final String name;
  final String category;
  final int questionCount;
  final int playedCount;
  final double progress;
  final int? remoteQuizId;
  final String? categoryColor;
  final String? categorySlug;

  String get cleanName => name.trim();
}

/// A category loaded from the Laravel `/api/categories` endpoint.
class QuizCategory {
  const QuizCategory({
    required this.id,
    required this.name,
    required this.slug,
    required this.color,
    required this.quizzesCount,
    this.description = '',
    this.imageUrl,
  });

  final int id;
  final String name;
  final String slug;
  final String color;
  final int quizzesCount;
  final String description;

  /// Admin-uploaded category image, or null if none has been set — the
  /// only acceptable behavior is one generic placeholder in that case,
  /// never a per-category hardcoded image (dynamic_quiz_category_images_brd.md §7).
  final String? imageUrl;

  factory QuizCategory.fromJson(Map<String, dynamic> json) => QuizCategory(
        id: (json['id'] as num).toInt(),
        name: json['name'] as String? ?? '',
        slug: json['slug'] as String? ?? '',
        color: json['color'] as String? ?? '#6900C5',
        quizzesCount: (json['quizzes_count'] as num?)?.toInt() ?? 0,
        description: json['description'] as String? ?? '',
        imageUrl: json['image_url'] as String?,
      );

  String get localizedName {
    if (AppLanguage.instance.isHindi) {
      final s = slug.toLowerCase().replaceAll('-', '_');
      if (s.contains('science')) return AppStrings.t('science');
      if (s.contains('math')) return AppStrings.t('maths');
      if (s.contains('gk') || s.contains('general')) return AppStrings.t('gk');
      if (s.contains('history') || s.contains('heritage')) return 'इतिहास';
      if (s.contains('evs')) return AppStrings.t('evs');
    }
    return name;
  }
}
