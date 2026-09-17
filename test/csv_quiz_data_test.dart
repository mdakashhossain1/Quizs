import 'package:flutter_test/flutter_test.dart';
import 'package:quizs/models/question_model.dart';

void main() {
  group('Remote Quiz and Question Models', () {
    test('QuizCategory parses from backend API response', () {
      final json = {
        'id': 1,
        'name': 'Science & Nature',
        'slug': 'science-nature',
        'description': 'Questions about physics, chemistry, biology',
        'icon': 'science',
        'color': '#4F46E5',
        'quizzes_count': 3,
        'image_url': 'https://example.com/uploads/categories/science.jpg',
      };

      final category = QuizCategory.fromJson(json);
      expect(category.id, equals(1));
      expect(category.name, equals('Science & Nature'));
      expect(category.slug, equals('science-nature'));
      expect(category.quizzesCount, equals(3));
      expect(category.color, equals('#4F46E5'));
      expect(category.imageUrl, equals('https://example.com/uploads/categories/science.jpg'));
    });

    test('QuizCategory has a null imageUrl when the backend has none set (dynamic_quiz_category_images_brd §7)', () {
      final category = QuizCategory.fromJson({
        'id': 2, 'name': 'GK', 'slug': 'gk', 'color': '#000', 'quizzes_count': 0,
      });

      expect(category.imageUrl, isNull);
    });

    test('QuizTopic holds remote quiz properties', () {
      const topic = QuizTopic(
        name: 'Solar System & Planets',
        category: 'Science',
        questionCount: 15,
        playedCount: 142,
        progress: 0.5,
        remoteQuizId: 10,
        categorySlug: 'science',
      );
      expect(topic.remoteQuizId, equals(10));
      expect(topic.cleanName, equals('Solar System & Planets'));
      expect(topic.category, equals('Science'));
      expect(topic.questionCount, equals(15));
      expect(topic.playedCount, equals(142));
    });

    test('Question parses correctly from backend remote JSON', () {
      final json = {
        'id': 101,
        'question': 'What is the capital of France?',
        'option_a': 'London',
        'option_b': 'Paris',
        'option_c': 'Berlin',
        'option_d': 'Madrid',
        'correct_option': 'B',
        'explanation': 'Paris is the capital of France.',
      };

      final question = Question.fromRemoteJson(json);
      expect(question.id, equals('101'));
      expect(question.question, equals('What is the capital of France?'));
      expect(question.optionA, equals('London'));
      expect(question.optionB, equals('Paris'));
      expect(question.options, equals(['London', 'Paris', 'Berlin', 'Madrid']));
      expect(question.correctOptionIndex, equals(1));
      expect(question.correctOption, equals('B'));
      expect(question.correctAnswer, equals('Paris'));
      expect(question.explanation, equals('Paris is the capital of France.'));
    });
  });
}
