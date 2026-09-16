import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/quiz_repository.dart';
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
  late bool _isLoading;

  @override
  void initState() {
    super.initState();
    _isLoading = !QuizRepository.instance.isLoaded;
    if (_isLoading) {
      QuizRepository.instance.initialize().then((_) {
        if (mounted) {
          setState(() {
            _isLoading = false;
          });
        }
      });
    }
  }



  @override
  Widget build(BuildContext context) {
    final isHindi = AppLanguage.instance.isHindi;
    final isTopicView = widget.mathematics || widget.categoryKey != null;
    final displayBottomNav = widget.showBottomNav ?? !isTopicView;

    final effectiveKey =
        widget.categoryKey ?? (widget.mathematics ? 'math' : 'math');
    final topics = isTopicView
        ? QuizRepository.instance.getTopicsForCategory(
            categoryKey: effectiveKey,
            isHindi: isHindi,
          )
        : QuizRepository.instance.getTrendingTopics(isHindi: isHindi);

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
    final int displayCount =
        isTopicView ? (_isLoading ? 6 : topics.length) : 4;

    final double lastCardBottom = startY + (displayCount * cardSpacing);
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
                    onTap: () {
                      final selectedTopic =
                          i < topics.length ? topics[i] : null;
                      Navigator.pushNamed(context, '/question',
                          arguments: selectedTopic);
                    },
                  ),
          ),
        ],
      ],
    );

  }
}


