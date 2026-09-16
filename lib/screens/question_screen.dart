import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../ads/test_ads.dart';
import '../l10n/app_strings.dart';
import '../models/question_model.dart';
import '../services/quiz_api_service.dart';
import '../widgets/design_widgets.dart';
import 'results_screen.dart';

class QuestionScreen extends StatefulWidget {
  const QuestionScreen({
    super.key,
    this.explanation = false,
    this.topic,
    this.questions,
  });

  final bool explanation;
  final QuizTopic? topic;
  final List<Question>? questions;

  @override
  State<QuestionScreen> createState() => _QuestionScreenState();
}

class _QuestionScreenState extends State<QuestionScreen> {
  late List<Question> _questions;
  int? _quizId;
  bool _isLoadingRemote = false;
  bool _remoteLoadError = false;
  int _currentIndex = 0;
  int? _selectedOption;
  bool _hasAnswered = false;
  final Map<int, int> _userAnswers = {};
  int _correctCount = 0;
  int _wrongCount = 0;
  int _countdown = 5;
  Timer? _countdownTimer;
  int _answeredInSessionCount = 0;

  @override
  void initState() {
    super.initState();
    final remoteQuizId = widget.topic?.remoteQuizId;
    if (remoteQuizId != null && (widget.topic?.questions.isEmpty ?? true)) {
      _quizId = remoteQuizId;
      _questions = const [];
      _loadRemoteQuestions(remoteQuizId);
    } else {
      _initQuiz();
    }
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadRemoteQuestions(int quizId) async {
    setState(() {
      _isLoadingRemote = true;
      _remoteLoadError = false;
    });
    try {
      final questions = await QuizApiService.instance.fetchQuizQuestions(quizId);
      if (!mounted) return;
      setState(() {
        _questions = questions.isNotEmpty ? questions : [_fallbackQuestion];
        _isLoadingRemote = false;
      });
    } catch (e) {
      debugPrint('Quiz load note: $e');
      if (!mounted) return;
      setState(() {
        _remoteLoadError = true;
        _isLoadingRemote = false;
      });
    }
  }

  void _initQuiz() {
    final isHindi = AppLanguage.instance.isHindi;
    final defaultMarsQ = Question(
      id: 'DEFAULT_01',
      language: isHindi ? 'Hindi' : 'English',
      category: isHindi ? 'विज्ञान' : 'Science',
      subtopic: isHindi ? 'ग्रह और सौरमंडल' : 'Planets & Solar System',
      difficulty: 'Easy',
      question: AppStrings.t('q_red_planet'),
      optionA: AppStrings.t('venus'),
      optionB: AppStrings.t('mercury'),
      optionC: AppStrings.t('mars'),
      optionD: AppStrings.t('jupiter'),
      correctOption: 'C',
      correctAnswer: AppStrings.t('mars'),
      meaning: AppStrings.t('mars_desc_title'),
      explanation: AppStrings.t('mars_desc_body'),
    );

    if (widget.questions != null && widget.questions!.isNotEmpty) {
      _questions = widget.questions!;
    } else if (widget.topic != null && widget.topic!.questions.isNotEmpty) {
      _questions = widget.topic!.questions;
    } else {
      _questions = [defaultMarsQ];
    }


    if (widget.explanation) {
      _selectedOption = 2; // Mars in test/explanation route
      _hasAnswered = true;
      _correctCount = 1;
    }
  }

  void _startCountdown() {
    _countdownTimer?.cancel();
    setState(() {
      _countdown = 5;
    });
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      if (_countdown > 1) {
        setState(() {
          _countdown--;
        });
      } else {
        timer.cancel();
        _nextQuestion();
      }
    });
  }

  void _selectOption(int index) {
    if (_hasAnswered) return;
    final currentQ = _currentQuestion;
    final isCorrect = index == currentQ.correctOptionIndex;

    setState(() {
      _selectedOption = index;
      _userAnswers[_currentIndex] = index;
      _hasAnswered = true;
      if (isCorrect) {
        _correctCount++;
      } else {
        _wrongCount++;
      }
    });

    _startCountdown();
  }

  Question get _currentQuestion =>
      _questions.isNotEmpty ? _questions[_currentIndex] : _fallbackQuestion;

  Question get _fallbackQuestion => Question(
        id: 'FALLBACK_01',
        language: 'English',
        category: 'Science',
        subtopic: 'Planets & Solar System',
        difficulty: 'Easy',
        question: AppStrings.t('q_red_planet'),
        optionA: AppStrings.t('venus'),
        optionB: AppStrings.t('mercury'),
        optionC: AppStrings.t('mars'),
        optionD: AppStrings.t('jupiter'),
        correctOption: 'C',
        correctAnswer: AppStrings.t('mars'),
        meaning: AppStrings.t('mars_desc_title'),
        explanation: AppStrings.t('mars_desc_body'),
      );

  void _nextQuestion() {
    _countdownTimer?.cancel();
    if (!_hasAnswered && _selectedOption == null) {
      setState(() {
        _selectedOption = _currentQuestion.correctOptionIndex;
        _userAnswers[_currentIndex] = _currentQuestion.correctOptionIndex;
        _hasAnswered = true;
        _wrongCount++;
      });
      _startCountdown();
      return;
    }

    _answeredInSessionCount++;
    if (_answeredInSessionCount % 2 == 0) {
      TestAds.showInterstitialAd(
        onComplete: () {
          if (!mounted) return;
          _proceedToNextOrFinish();
        },
      );
    } else {
      _proceedToNextOrFinish();
    }
  }

  void _proceedToNextOrFinish() {
    if (_currentIndex < _questions.length - 1) {
      setState(() {
        _currentIndex++;
        _selectedOption = _userAnswers[_currentIndex];
        _hasAnswered = _selectedOption != null;
        _countdown = 5;
      });
      if (_hasAnswered) {
        _startCountdown();
      }
    } else {
      _finishQuiz();
    }
  }

  void _previousQuestion() {
    _countdownTimer?.cancel();
    if (_currentIndex > 0) {
      setState(() {
        _currentIndex--;
        _selectedOption = _userAnswers[_currentIndex];
        _hasAnswered = _selectedOption != null;
        _countdown = 5;
      });
    } else {
      Navigator.of(context).pop();
    }
  }

  void _finishQuiz() async {
    _countdownTimer?.cancel();
    var total = _questions.length;
    var right = _correctCount;
    var wrong = _wrongCount > 0 ? _wrongCount : (total - right);
    var pct = total > 0 ? ((right / total) * 100).round() : 0;

    final quizId = _quizId;
    if (quizId != null &&
        _questions.isNotEmpty &&
        _questions.every((q) => q.remoteId != null)) {
      final answers = <int, int>{};
      for (var i = 0; i < _questions.length; i++) {
        final selectedIndex = _userAnswers[i];
        final optionIds = _questions[i].remoteOptionIds;
        if (selectedIndex != null &&
            optionIds != null &&
            selectedIndex < optionIds.length) {
          answers[_questions[i].remoteId!] = optionIds[selectedIndex];
        }
      }

      try {
        final result = await QuizApiService.instance.submitQuiz(
          quizId: quizId,
          answers: answers,
        );
        final serverTotal = (result['total_questions'] as num?)?.toInt();
        final serverRight = (result['correct_answers'] as num?)?.toInt();
        if (serverTotal != null && serverRight != null) {
          total = serverTotal;
          right = serverRight;
          wrong = serverTotal - serverRight;
          pct = (result['percentage'] as num?)?.round() ?? pct;
        }
      } catch (e) {
        // Network dropped right at the end: still show the locally tallied
        // result rather than losing the user's finished quiz.
        debugPrint('Quiz submit note: $e');
      }
    }

    if (!mounted) return;
    Navigator.pushNamed(
      context,
      '/results',
      arguments: QuizResultArgs(
        totalSolved: total,
        rightCount: right,
        wrongCount: wrong,
        scorePercentage: pct,
      ),
    );
  }

  double _calculateExplanationHeight(
    String title,
    String body,
    double textWidth,
  ) {
    final titlePainter = TextPainter(
      text: TextSpan(
        text: title,
        style: const TextStyle(
          fontFamily: 'Poppins',
          fontSize: 14.5,
          fontWeight: FontWeight.w700,
          height: 1.25,
        ),
      ),
      textDirection: TextDirection.ltr,
    )..layout(maxWidth: textWidth - 8.0);

    final bodyPainter = TextPainter(
      text: TextSpan(
        text: body,
        style: const TextStyle(
          fontFamily: 'Poppins',
          fontSize: 11.5,
          fontWeight: FontWeight.w500,
          height: 1.35,
        ),
      ),
      textDirection: TextDirection.ltr,
    )..layout(maxWidth: textWidth - 8.0);

    final contentHeight = titlePainter.height + 10.0 + bodyPainter.height;
    return math.max(137.0, contentHeight + 48.0);
  }

  double _calculateQuestionCardHeight(
    String qText,
    List<String> options,
    double cardContentWidth,
  ) {
    final qPainter = TextPainter(
      text: TextSpan(
        text: qText,
        style: const TextStyle(
          fontFamily: 'Poppins',
          fontSize: 16.5,
          fontWeight: FontWeight.w600,
          height: 1.3,
        ),
      ),
      textDirection: TextDirection.ltr,
    )..layout(maxWidth: cardContentWidth - 8.0);

    double optionsHeight = 0;
    final optionTextWidth = cardContentWidth - 28.0 - 40.0;

    for (var i = 0; i < 4; i++) {
      final optText = i < options.length ? options[i] : '';
      final optPainter = TextPainter(
        text: TextSpan(
          text: optText,
          style: const TextStyle(
            fontFamily: 'Poppins',
            fontSize: 15.0,
            fontWeight: FontWeight.w600,
            height: 1.25,
          ),
        ),
        textDirection: TextDirection.ltr,
      )..layout(maxWidth: optionTextWidth);

      final itemH = math.max(48.0, optPainter.height + 26.0);
      optionsHeight += itemH;
      if (i < 3) optionsHeight += 10.0;
    }

    final totalHeight = 24.0 + 10.0 + qPainter.height + 16.0 + optionsHeight + 36.0 + 24.0;
    return math.max(416.0, totalHeight);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoadingRemote) {
      return const Scaffold(
        backgroundColor: Colors.white,
        body: Center(
          child: CircularProgressIndicator(color: QuizColors.purple),
        ),
      );
    }

    if (_remoteLoadError) {
      return Scaffold(
        backgroundColor: Colors.white,
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.wifi_off_rounded, color: QuizColors.purple, size: 40),
              const SizedBox(height: 12),
              const Text(
                'Could not load this quiz. Check your connection and try again.',
                textAlign: TextAlign.center,
                style: TextStyle(fontFamily: 'Poppins', fontSize: 13.5, color: Color(0xFF757575)),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () => _loadRemoteQuestions(_quizId!),
                style: ElevatedButton.styleFrom(backgroundColor: QuizColors.purple),
                child: const Text('Retry', style: TextStyle(color: Colors.white)),
              ),
              TextButton(
                onPressed: () => Navigator.of(context).pop(),
                child: const Text('Go back'),
              ),
            ],
          ),
        ),
      );
    }

    final isExpl = widget.explanation || _hasAnswered;
    final currentQ = _currentQuestion;
    final options = currentQ.options;
    final isHindi = AppLanguage.instance.isHindi;

    final explTitle = currentQ.meaning.isNotEmpty
        ? currentQ.meaning
        : (currentQ.correctAnswer.isNotEmpty
            ? '${currentQ.correctAnswer}- ${AppStrings.t("mars_desc_title")}'
            : AppStrings.t('mars_desc_title'));

    final explBody = currentQ.explanation.isNotEmpty
        ? currentQ.explanation
        : AppStrings.t('mars_desc_body');

    final qText = currentQ.question;

    // Calculate dynamic height for Question Card
    const double cardWidth = 320.0;
    const double cardX = 46.0;
    const double cardY = 117.0;
    final double cardHeight =
        _calculateQuestionCardHeight(qText, options, cardWidth - 44.0);

    // Dynamic vertical positions based on Question Card height
    final double navY = cardY + cardHeight + 28.0;
    final double explY = navY + 31.0;

    const double explCardWidth = 320.0;
    const double explTextWidth = explCardWidth - 28.0 - 56.0 - 14.0; // 222px
    final double explCardHeight =
        _calculateExplanationHeight(explTitle, explBody, explTextWidth);

    final double progressY =
        isExpl ? (explY + explCardHeight + 23.0) : (navY + 117.0);
    final double controlsY = progressY + 20.0;
    final double adAfterY = controlsY + 48.0;

    return DesignCanvas(
      adAfter: adAfterY,
      topColor: const Color(0xFF31005C),
      children: [
        const Positioned.fill(child: QuestionBackground()),
        label(
          'Quizs',
          159,
          24,
          32,
          color: Colors.white,
          family: 'Quizlo',
          lineHeight: 1.44,
        ),
        backButton(context, compact: true),

        // Layered Question Card Stack (Dynamically adapts to cardHeight)
        panel(
          59,
          131,
          294,
          cardHeight,
          const Color(0xFF9C34F7),
          radius: 18,
        ),
        panel(
          51,
          125,
          310,
          cardHeight,
          const Color(0xFFD7AAFF),
          radius: 18,
        ),
        panel(
          cardX,
          cardY,
          cardWidth,
          cardHeight,
          Colors.white,
          radius: 22,
        ),

        // Flexible Question Card Content (No clipping, completely adaptive)
        at(
          cardX,
          cardY,
          cardWidth,
          cardHeight,
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                // Category Header
                Text(
                  AppStrings.t('questions_header'),
                  style: const TextStyle(
                    fontFamily: 'Questrial',
                    fontSize: 18,
                    color: Color(0x52000000),
                    height: 1.1,
                  ),
                ),
                const SizedBox(height: 10),

                // Question Prompt (Grows naturally to any length)
                Text(
                  qText,
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 16.5,
                    fontWeight: FontWeight.w600,
                    color: Colors.black,
                    height: 1.3,
                  ),
                ),
                const SizedBox(height: 16),

                // 4 Flexible Option Boxes
                for (var i = 0; i < 4; i++) ...[
                  () {
                    final isSelected = _selectedOption == i;
                    final isCorrect = i == currentQ.correctOptionIndex;
                    final optTitle = i < options.length ? options[i] : '';

                    Color bgColor = Colors.white;
                    Color borderColor = const Color(0xFFEDEDED);
                    Widget? badgeWidget;

                    if (_hasAnswered) {
                      if (isSelected && isCorrect) {
                        bgColor = const Color(0xFF8CE7BA);
                        borderColor = const Color(0xFF1ACE50);
                        badgeWidget = SizedBox(
                          width: 28,
                          height: 28,
                          child: Stack(
                            alignment: Alignment.center,
                            children: [
                              const FigmaAsset('7-63_imgEllipse3.svg'),
                              Center(
                                child: Image.asset(
                                  'assets/figma/7-63_imgCheck2.png',
                                  width: 17,
                                  height: 17,
                                  color: Colors.white,
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ],
                          ),
                        );
                      } else if (isSelected && !isCorrect) {
                        bgColor = const Color(0xFFFFCDD2);
                        borderColor = const Color(0xFFE53935);
                        badgeWidget = Container(
                          width: 28,
                          height: 28,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            color: Color(0xFFE53935),
                          ),
                          child: const Center(
                            child: Icon(
                              Icons.close,
                              color: Colors.white,
                              size: 18,
                            ),
                          ),
                        );
                      } else if (!isSelected && isCorrect) {
                        bgColor = const Color(0xFF8CE7BA);
                        borderColor = const Color(0xFF1ACE50);
                        badgeWidget = SizedBox(
                          width: 28,
                          height: 28,
                          child: Stack(
                            alignment: Alignment.center,
                            children: [
                              const FigmaAsset('7-63_imgEllipse3.svg'),
                              Center(
                                child: Image.asset(
                                  'assets/figma/7-63_imgCheck2.png',
                                  width: 17,
                                  height: 17,
                                  color: Colors.white,
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ],
                          ),
                        );
                      }
                    }

                    return Container(
                      margin: EdgeInsets.only(bottom: i < 3 ? 10 : 0),
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: () => _selectOption(i),
                          borderRadius: BorderRadius.circular(9),
                          child: Container(
                            constraints: const BoxConstraints(minHeight: 48),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 11,
                            ),
                            decoration: BoxDecoration(
                              color: bgColor,
                              borderRadius: BorderRadius.circular(9),
                              border: Border.all(
                                color: borderColor,
                                width: 1.2,
                              ),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    optTitle,
                                    style: const TextStyle(
                                      fontFamily: 'Poppins',
                                      fontSize: 15,
                                      fontWeight: FontWeight.w600,
                                      color: Color(0xFF1E1E1E),
                                      height: 1.25,
                                    ),
                                  ),
                                ),
                                if (badgeWidget != null) ...[
                                  const SizedBox(width: 8),
                                  badgeWidget,
                                ],
                              ],
                            ),
                          ),
                        ),
                      ),
                    );
                  }(),
                ],
              ],
            ),
          ),
        ),

        // Invisible Semantic Actions for test accessibility
        for (var i = 0; i < 4; i++)
          at(
            cardX,
            cardY + 100.0 + (i * 50.0),
            cardWidth,
            48,
            DesignAction(
              label: 'View answer explanation for ${i < options.length ? options[i] : ""}',
              onTap: () => _selectOption(i),
            ),
          ),

        // Previous & Next Controls (Positioned dynamically below Question card)
        label(
          AppStrings.t('previous'),
          57,
          navY,
          14,
          color: Colors.white,
          weight: FontWeight.w600,
          lineHeight: 1.25,
        ),
        label(
          AppStrings.t('next'),
          321,
          navY,
          14,
          color: Colors.white,
          weight: FontWeight.w600,
          lineHeight: 1.25,
        ),
        at(
          45,
          navY - 15,
          95,
          44,
          DesignAction(
            label: 'Previous question',
            onTap: _previousQuestion,
          ),
        ),
        at(
          302,
          navY - 15,
          65,
          44,
          DesignAction(
            label: 'Next question',
            onTap: _nextQuestion,
          ),
        ),

        // Entire Explanation Card (Expands dynamically to fit full text)
        if (isExpl) ...[
          at(
            cardX,
            explY,
            explCardWidth,
            explCardHeight,
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x1A000000),
                    offset: Offset(0, 4),
                    blurRadius: 12,
                  ),
                ],
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      color: const Color(0xFF109E3B),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.lightbulb_outline,
                        color: Colors.white,
                        size: 32,
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          explTitle,
                          style: const TextStyle(
                            fontFamily: 'Poppins',
                            fontSize: 14.5,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1E1E1E),
                            height: 1.25,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          explBody,
                          style: const TextStyle(
                            fontFamily: 'Poppins',
                            fontSize: 11.5,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF333333),
                            height: 1.35,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],

        // Functional Animated Progress Bar
        at(
          56,
          progressY,
          298,
          7,
          Stack(
            children: [
              // Base Track
              Container(
                width: 298,
                height: 7,
                decoration: BoxDecoration(
                  color: const Color(0xFF8A00E5),
                  borderRadius: BorderRadius.circular(22),
                ),
              ),
              // Animated Dynamic Question Progress Fill (Out of total questions)
              AnimatedContainer(
                duration: const Duration(milliseconds: 350),
                curve: Curves.easeOutCubic,
                width: _questions.isNotEmpty
                    ? (298.0 * ((_currentIndex + 1) / _questions.length))
                        .clamp(20.0, 298.0)
                    : 127.0,
                height: 7,
                decoration: BoxDecoration(
                  color: const Color(0xFF00FF96),
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x6600FF96),
                      blurRadius: 4,
                      offset: Offset(0, 1),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        label(
          _countdown == 5
              ? AppStrings.t('next_trial_in_5s')
              : (isHindi ? 'अगला प्रयास ${_countdown}s में' : 'Next Trial in ${_countdown}s'),
          64,
          controlsY + 8,
          14,
          color: Colors.white,
          weight: FontWeight.w600,
          lineHeight: 1.25,
        ),
        // Pure Native Flutter Next Trial Button (No SVG/Image dependency)
        at(
          193,
          controlsY,
          152,
          33,
          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: _nextQuestion,
              borderRadius: BorderRadius.circular(8),
              child: Container(
                width: 152,
                height: 33,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF5CF3A8), Color(0xFF33DA87)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                  borderRadius: BorderRadius.circular(8),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x3300C853),
                      blurRadius: 8,
                      offset: Offset(0, 3),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    AppStrings.t('next_trial'),
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      height: 1.25,
                      letterSpacing: 0.2,
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
        at(
          190,
          controlsY - 5,
          158,
          44,
          DesignAction(
            label: AppStrings.t('next_trial'),
            onTap: _nextQuestion,
          ),
        ),
      ],
    );
  }
}
