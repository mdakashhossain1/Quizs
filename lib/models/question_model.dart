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
}

class QuizTopic {
  const QuizTopic({
    required this.name,
    required this.category,
    required this.questionCount,
    required this.playedCount,
    required this.progress,
    required this.questions,
  });

  final String name;
  final String category;
  final int questionCount;
  final int playedCount;
  final double progress;
  final List<Question> questions;

  String get cleanName => name.trim();
}

