import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../models/question_model.dart';
import '../services/quiz_api_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';
import '../widgets/shimmer_loading.dart';

class SelectionScreen extends StatefulWidget {
  const SelectionScreen({
    super.key,
    this.mathematics = false,
    this.categoryKey,
    this.title,
    this.showBottomNav,
  });

  final bool mathematics;
  final String? categoryKey;
  final String? title;
  final bool? showBottomNav;

  @override
  State<SelectionScreen> createState() => _SelectionScreenState();
}

class _SelectionScreenState extends State<SelectionScreen> {
  bool _isLoading = true;
  bool _hasError = false;
  List<QuizTopic> _topics = const [];

  bool get _isTopicView => widget.mathematics || widget.categoryKey != null;

  String get _effectiveKey =>
      widget.categoryKey ?? (widget.mathematics ? 'math' : 'math');

  @override
  void initState() {
    super.initState();
    AppLanguage.instance.addListener(_loadTopics);
    _loadTopics();
  }

  @override
  void dispose() {
    AppLanguage.instance.removeListener(_loadTopics);
    super.dispose();
  }

  Future<void> _loadTopics() async {
    setState(() {
      _isLoading = true;
      _hasError = false;
    });
    try {
      final isHindi = AppLanguage.instance.isHindi;
      final topics = _isTopicView
          ? await QuizApiService.instance.getTopicsForCategory(
              categoryKey: _effectiveKey,
              isHindi: isHindi,
            )
          : await QuizApiService.instance.getTrendingTopics(isHindi: isHindi);
      if (!mounted) return;
      setState(() {
        _topics = topics;
        _isLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _hasError = true;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final isHindi = AppLanguage.instance.isHindi;
    final isTopicView = _isTopicView;
    final displayBottomNav = widget.showBottomNav ?? !isTopicView;
    final effectiveKey = _effectiveKey;
    final topics = _topics;

    final String headingText;
    if (widget.title != null) {
      headingText = widget.title!;
    } else if (widget.mathematics ||
        effectiveKey == 'math' ||
        effectiveKey == 'mathematics') {
      headingText = AppStrings.t('mathematics');
    } else if (effectiveKey == 'science') {
      headingText = AppStrings.t('science');
    } else if (effectiveKey == 'gk') {
      headingText = AppStrings.t('gk');
    } else {
      headingText = AppStrings.t('choose_category');
    }

    final double startY = isTopicView ? 218.0 : 472.0;
    const double cardSpacing = 90.0;
    final int displayCount = _hasError
        ? 0
        : isTopicView
            ? (_isLoading ? 6 : topics.length)
            : 4;

    final double errorBlockHeight = _hasError ? 140.0 : 0.0;
    final double lastCardBottom =
        startY + (displayCount * cardSpacing) + errorBlockHeight;
    final double panelHeight = isTopicView
        ? (lastCardBottom - 192.0 + 36.0)
        : (lastCardBottom - 187.0 + 26.0);
    final double adAfterY = lastCardBottom + 12.0;
    final double adTopY = lastCardBottom + 20.0;

    return DesignCanvas(
      adTop: adTopY,
      adAfter: adAfterY,
      bottomNav: displayBottomNav
          ? QuizBottomNav(
              initialIndex: 1,
              onTabSelected: (index) {
                if (index == 0) {
                  Navigator.pushNamedAndRemoveUntil(
                      context, '/', (route) => false);
                } else if (index == 2) {
                  Navigator.pushReplacementNamed(context, '/profile');
                }
              },
            )
          : null,
      children: [
        at(0, -139, 412, 467, const PurpleHeader()),
        backButton(context),
        label(
          isTopicView ? headingText : AppStrings.t('choose_category'),
          0,
          isTopicView ? 122 : 126,
          isTopicView ? 36 : 26,
          width: 412,
          align: TextAlign.center,
          color: Colors.white,
          weight: isTopicView ? FontWeight.w800 : FontWeight.w700,
        ),
        panel(
          0,
          isTopicView ? 192 : 187,
          412,
          panelHeight,
          Colors.white,
          radius: isTopicView ? 27 : 31,
        ),
        if (!isTopicView) ...[
          ...categories(context, 212),
          ...categories(context, 324),
          label(
            AppStrings.t('trending_quizzes'),
            26,
            436,
            20,
            color: QuizColors.purple,
            weight: FontWeight.w800,
          ),
        ],
        if (_hasError)
          at(
            24,
            startY,
            364,
            errorBlockHeight,
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.wifi_off_rounded, color: QuizColors.purple, size: 32),
                const SizedBox(height: 10),
                Text(
                  'Could not load quizzes. Check your connection and try again.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 12.5,
                    color: Color(0xFF757575),
                  ),
                ),
                const SizedBox(height: 12),
                GestureDetector(
                  onTap: _loadTopics,
                  child: Text(
                    'Retry',
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: QuizColors.purple,
                      decoration: TextDecoration.underline,
                    ),
                  ),
                ),
              ],
            ),
          ),
        for (var i = 0; i < displayCount; i++) ...[
          at(
            22,
            startY + (i * cardSpacing),
            367,
            80,
            _isLoading
                ? const QuizCardShimmer()
                : QuizCard(
                    index: i + 1,
                    title: i < topics.length ? topics[i].cleanName : null,
                    questionCount: i < topics.length
                        ? (isHindi
                            ? '${topics[i].questionCount} प्रश्न'
                            : '${topics[i].questionCount} Questions')
                        : null,
                    playedCount: i < topics.length
                        ? '${topics[i].playedCount}'
                        : '${320 + i * 45}',
                    progress: i < topics.length
                        ? topics[i].progress
                        : (0.3 + i * 0.15),
                    onTap: i < topics.length
                        ? () {
                            Navigator.pushNamed(context, '/question',
                                arguments: topics[i]);
                          }
                        : null,
                  ),
          ),
        ],
      ],
    );
  }
}
