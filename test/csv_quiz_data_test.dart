import 'package:flutter_test/flutter_test.dart';
import 'package:quizs/services/quiz_repository.dart';


void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUpAll(() async {
    await QuizRepository.instance.initialize();
  });

  group('QuizRepository CSV loading and topic data', () {
    test('loads GK topics and questions authentically from CSV', () {
      final gkTopics = QuizRepository.instance.getTopicsForCategory(categoryKey: 'gk');
      expect(gkTopics.isNotEmpty, isTrue);

      // Verify authentic subtopic names exist
      final topicNames = gkTopics.map((t) => t.cleanName).toList();
      expect(topicNames, contains('Ancient & Medieval History'));
      expect(topicNames, contains('Modern Indian History & Freedom Struggle'));
      expect(topicNames, contains('World History & Revolutions'));
      expect(topicNames, contains('Indian Geography (Rivers, Mountains, Climate)'));

      // Check first topic details
      final ancientTopic = gkTopics.firstWhere((t) => t.cleanName == 'Ancient & Medieval History');
      expect(ancientTopic.questionCount, greaterThan(10));
      expect(ancientTopic.questions.length, equals(ancientTopic.questionCount));

      final firstQ = ancientTopic.questions.first;
      expect(firstQ.question.isNotEmpty, isTrue);
      expect(firstQ.options.length, equals(4));
      expect(firstQ.correctOption.isNotEmpty, isTrue);
      expect(firstQ.explanation.isNotEmpty, isTrue);
    });

    test('loads Math and Science topics from CSV', () {
      final mathTopics = QuizRepository.instance.getTopicsForCategory(categoryKey: 'math');
      expect(mathTopics.isNotEmpty, isTrue);
      expect(mathTopics.length, greaterThan(5));

      final scienceTopics = QuizRepository.instance.getTopicsForCategory(categoryKey: 'science');
      expect(scienceTopics.isNotEmpty, isTrue);
      expect(scienceTopics.length, greaterThan(5));
    });
  });
}
