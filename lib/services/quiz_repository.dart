import 'package:flutter/services.dart' show rootBundle;

import '../models/question_model.dart';

class QuizRepository {
  QuizRepository._();
  static final QuizRepository instance = QuizRepository._();

  final Map<String, List<Question>> _cachedQuestions = {};
  bool _isLoaded = false;

  Future<void> initialize() async {
    if (_isLoaded) return;
    try {
      await Future.wait([
        _loadCsv('assets/questions/questions_math_english.csv', 'math_en'),
        _loadCsv('assets/questions/questions_math_hindi.csv', 'math_hi'),
        _loadCsv('assets/questions/questions_science_english.csv', 'science_en'),
        _loadCsv('assets/questions/questions_science_hindi.csv', 'science_hi'),
        _loadCsv('assets/questions/questions_gk_english.csv', 'gk_en'),
        _loadCsv('assets/questions/questions_gk_hindi.csv', 'gk_hi'),
      ]);
      _isLoaded = true;
    } catch (_) {
      // Graceful fallback for environments where rootBundle is mock/limited
    }
  }

  Future<void> _loadCsv(String assetPath, String cacheKey) async {
    try {
      final raw = await rootBundle.loadString(assetPath);
      final rows = _parseCsv(raw);
      if (rows.isEmpty) return;

      final questions = <Question>[];
      // Skip header row
      for (var i = 1; i < rows.length; i++) {
        final row = rows[i];
        if (row.length >= 12 && row[5].trim().isNotEmpty) {
          questions.add(Question.fromCsvRow(row));
        }
      }
      _cachedQuestions[cacheKey] = questions;
    } catch (_) {
      // Ignore load error
    }
  }

  List<List<String>> _parseCsv(String input) {
    final results = <List<String>>[];
    final currentField = StringBuffer();
    final currentRow = <String>[];
    var insideQuotes = false;

    for (var i = 0; i < input.length; i++) {
      final char = input[i];

      if (insideQuotes) {
        if (char == '"') {
          if (i + 1 < input.length && input[i + 1] == '"') {
            currentField.write('"');
            i++; // skip escaped quote
          } else {
            insideQuotes = false;
          }
        } else {
          currentField.write(char);
        }
      } else {
        if (char == '"') {
          insideQuotes = true;
        } else if (char == ',') {
          currentRow.add(currentField.toString().trim());
          currentField.clear();
        } else if (char == '\n' || char == '\r') {
          if (char == '\r' && i + 1 < input.length && input[i + 1] == '\n') {
            i++;
          }
          currentRow.add(currentField.toString().trim());
          currentField.clear();
          if (currentRow.any((s) => s.isNotEmpty)) {
            results.add(List<String>.from(currentRow));
          }
          currentRow.clear();
        } else {
          currentField.write(char);
        }
      }
    }

    if (currentField.isNotEmpty || currentRow.isNotEmpty) {
      currentRow.add(currentField.toString().trim());
      if (currentRow.any((s) => s.isNotEmpty)) {
        results.add(currentRow);
      }
    }

    return results;
  }

  bool get isLoaded => _isLoaded;

  String _normalizeCategory(String categoryKey) {
    final cat = categoryKey.toLowerCase().trim();
    if (cat.startsWith('math')) return 'math';
    if (cat.startsWith('sci')) return 'science';
    return 'gk';
  }

  List<Question> getQuestions({
    required String categoryKey,
    bool isHindi = false,
  }) {
    final cleanKey = _normalizeCategory(categoryKey);
    final key = '${cleanKey}_${isHindi ? "hi" : "en"}';
    return _cachedQuestions[key] ?? _fallbackQuestions(cleanKey, isHindi);
  }

  List<QuizTopic> getTopicsForCategory({
    required String categoryKey,
    bool isHindi = false,
  }) {
    final allQuestions = getQuestions(categoryKey: categoryKey, isHindi: isHindi);
    if (allQuestions.isEmpty) {
      return _fallbackTopics(categoryKey, isHindi);
    }

    // Group by authentic subtopic directly from the CSV questions
    final Map<String, List<Question>> grouped = {};
    for (final q in allQuestions) {
      final sub = q.subtopic.trim();
      if (sub.isNotEmpty && !sub.toLowerCase().contains('sample')) {
        grouped.putIfAbsent(sub, () => []).add(q);
      }
    }

    if (grouped.isEmpty) {
      return _fallbackTopics(categoryKey, isHindi);
    }

    final result = <QuizTopic>[];
    var count = 0;
    grouped.forEach((subtopic, questions) {
      final played = 180 + ((subtopic.hashCode.abs() % 350));
      final progress = ((count % 3) == 0) ? 0.45 : ((count % 3 == 1) ? 0.75 : 0.2);
      result.add(QuizTopic(
        name: subtopic,
        category: categoryKey,
        questionCount: questions.length,
        playedCount: played,
        progress: progress,
        questions: questions,
      ));
      count++;
    });

    return result;
  }

  List<QuizTopic> getTrendingTopics({bool isHindi = false}) {
    final mathTopics = getTopicsForCategory(categoryKey: 'math', isHindi: isHindi);
    final sciTopics = getTopicsForCategory(categoryKey: 'science', isHindi: isHindi);
    final gkTopics = getTopicsForCategory(categoryKey: 'gk', isHindi: isHindi);

    final list = <QuizTopic>[];
    if (mathTopics.isNotEmpty) list.add(mathTopics[0]);
    if (sciTopics.isNotEmpty) list.add(sciTopics[0]);
    if (gkTopics.isNotEmpty) list.add(gkTopics[0]);
    if (mathTopics.length > 1) {
      list.add(mathTopics[1]);
    } else if (gkTopics.length > 1) {
      list.add(gkTopics[1]);
    }

    if (list.isEmpty) {
      return _fallbackTopics('math', isHindi).take(4).toList();
    }
    return list;
  }


  List<QuizTopic> _fallbackTopics(String categoryKey, bool isHindi) {
    if (categoryKey == 'math' || categoryKey == 'mathematics') {
      return [
        QuizTopic(
          name: isHindi ? 'त्रिकोणमिति' : 'Trigonometry',
          category: 'Mathematics',
          questionCount: 10,
          playedCount: 320,
          progress: 0.45,
          questions: _fallbackQuestions('math', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'प्रतिशत व लाभ-हानि' : 'Percentages & Profit',
          category: 'Mathematics',
          questionCount: 10,
          playedCount: 285,
          progress: 0.60,
          questions: _fallbackQuestions('math', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'रैखिक व द्विघात समीकरण' : 'Linear Equations',
          category: 'Mathematics',
          questionCount: 10,
          playedCount: 410,
          progress: 0.30,
          questions: _fallbackQuestions('math', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'ज्यामिति व वृत्त' : 'Geometry & Circles',
          category: 'Mathematics',
          questionCount: 10,
          playedCount: 195,
          progress: 0.80,
          questions: _fallbackQuestions('math', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'समय, कार्य और चाल' : 'Time & Distance',
          category: 'Mathematics',
          questionCount: 10,
          playedCount: 350,
          progress: 0.50,
          questions: _fallbackQuestions('math', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'संख्या पद्धति व भिन्न' : 'Number Systems',
          category: 'Mathematics',
          questionCount: 10,
          playedCount: 240,
          progress: 0.20,
          questions: _fallbackQuestions('math', isHindi),
        ),
      ];
    } else if (categoryKey == 'science') {
      return [
        QuizTopic(
          name: isHindi ? 'ग्रह और सौरमंडल' : 'Astronomy & Planets',
          category: 'Science',
          questionCount: 10,
          playedCount: 420,
          progress: 0.70,
          questions: _fallbackQuestions('science', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'प्रकाश और प्रकाशिकी' : 'Light & Optics',
          category: 'Science',
          questionCount: 10,
          playedCount: 310,
          progress: 0.40,
          questions: _fallbackQuestions('science', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'मानव शरीर क्रिया विज्ञान' : 'Human Anatomy & Physiology',
          category: 'Science',
          questionCount: 10,
          playedCount: 270,
          progress: 0.55,
          questions: _fallbackQuestions('science', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'अम्ल, क्षार और लवण' : 'Acids, Bases & Salts',
          category: 'Science',
          questionCount: 10,
          playedCount: 190,
          progress: 0.35,
          questions: _fallbackQuestions('science', isHindi),
        ),
      ];
    } else {
      return [
        QuizTopic(
          name: isHindi ? 'भारतीय इतिहास व स्वतंत्रता' : 'Indian History & Freedom',
          category: 'GK',
          questionCount: 10,
          playedCount: 510,
          progress: 0.65,
          questions: _fallbackQuestions('gk', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'भारतीय संविधान व राजव्यवस्था' : 'Indian Polity & Constitution',
          category: 'GK',
          questionCount: 10,
          playedCount: 380,
          progress: 0.50,
          questions: _fallbackQuestions('gk', isHindi),
        ),
        QuizTopic(
          name: isHindi ? 'विश्व भूगोल व नदियाँ' : 'World Geography & Rivers',
          category: 'GK',
          questionCount: 10,
          playedCount: 290,
          progress: 0.30,
          questions: _fallbackQuestions('gk', isHindi),
        ),
      ];
    }
  }

  List<Question> _fallbackQuestions(String categoryKey, bool isHindi) {
    if (isHindi) {
      return [
        const Question(
          id: 'SCI_HI_0001',
          language: 'Hindi',
          category: 'Science',
          subtopic: 'ग्रह और सौरमंडल',
          difficulty: 'Easy',
          question: 'किस ग्रह को लाल ग्रह के नाम से जाना जाता है?',
          optionA: 'शुक्र',
          optionB: 'बुध',
          optionC: 'मंगल',
          optionD: 'बृहस्पति',
          correctOption: 'C',
          correctAnswer: 'मंगल',
          meaning: 'मंगल- लाल ग्रह',
          explanation: 'मंगल की सतह पर जंग जैसी धूल के कारण इसका रंग लाल दिखता है।',
        ),
        const Question(
          id: 'MATH_HI_0001',
          language: 'Hindi',
          category: 'Mathematics',
          subtopic: 'त्रिकोणमिति',
          difficulty: 'Medium',
          question: 'sin²θ + cos²θ का मान क्या होता है?',
          optionA: '0',
          optionB: '1',
          optionC: '2',
          optionD: '-1',
          correctOption: 'B',
          correctAnswer: '1',
          meaning: 'यह त्रिकोणमिति की मौलिक सर्वसमिका है।',
          explanation: 'पाइथागोरस प्रमेय के अनुसार sin²θ + cos²θ सदैव 1 होता है।',
        ),
      ];
    } else {
      return [
        const Question(
          id: 'SCI_EN_0001',
          language: 'English',
          category: 'Science',
          subtopic: 'Planets & Solar System',
          difficulty: 'Easy',
          question: 'Which planet is known as the Red Planet?',
          optionA: 'Venus',
          optionB: 'Mercury',
          optionC: 'Mars',
          optionD: 'Jupiter',
          correctOption: 'C',
          correctAnswer: 'Mars',
          meaning: 'Mars- The red planet',
          explanation: 'Mars is called the Red Planet because iron minerals in its soil oxidize.',
        ),
        const Question(
          id: 'MATH_EN_0001',
          language: 'English',
          category: 'Mathematics',
          subtopic: 'Trigonometry',
          difficulty: 'Medium',
          question: 'What is the value of sin²θ + cos²θ?',
          optionA: '0',
          optionB: '1',
          optionC: '2',
          optionD: '-1',
          correctOption: 'B',
          correctAnswer: '1',
          meaning: 'Fundamental Pythagorean trigonometric identity.',
          explanation: 'By the Pythagorean theorem applied to unit circles, sin²θ + cos²θ = 1.',
        ),
      ];
    }
  }
}
