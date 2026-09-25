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
    this.categoryId,
    this.categorySlug,
    this.title,
    this.showBottomNav,
  });

  /// When set, shows quizzes for this specific backend category id.
  final int? categoryId;

  /// When set (legacy route like /science), resolves to a category by slug.
  final String? categorySlug;

  final String? title;
  final bool? showBottomNav;

  @override
  State<SelectionScreen> createState() => _SelectionScreenState();
}

class _SelectionScreenState extends State<SelectionScreen> {
  bool _isLoading = true;
  bool _hasError = false;

  /// Topics (quizzes) when a specific category is selected.
  List<QuizTopic> _topics = const [];

  /// All categories for the browse view (loaded from backend).
  List<QuizCategory> _categories = const [];

  /// Trending topics (quizzes) for the browse view (loaded from backend).
  List<QuizTopic> _trendingTopics = const [];

  bool get _isCategoryView =>
      widget.categoryId != null || widget.categorySlug != null;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _hasError = false;
    });
    try {
      if (_isCategoryView) {
        final topics = widget.categoryId != null
            ? await QuizApiService.instance
                .getTopicsForCategoryId(widget.categoryId!)
            : await QuizApiService.instance
                .getTopicsForSlug(widget.categorySlug!);
        if (!mounted) return;
        setState(() {
          _topics = topics;
          _isLoading = false;
        });
      } else {
        // Browse view — fetch both live categories and trending quizzes from the backend
        final results = await Future.wait([
          QuizApiService.instance.getCategories(forceRefresh: true),
          QuizApiService.instance.getTrendingTopics(),
        ]);
        if (!mounted) return;
        setState(() {
          _categories = results[0] as List<QuizCategory>;
          _trendingTopics = results[1] as List<QuizTopic>;
          _isLoading = false;
        });
      }
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
    final displayBottomNav = widget.showBottomNav ?? !_isCategoryView;
    final headingText = widget.title ??
        (_isCategoryView
            ? AppStrings.t('choose_category')
            : AppStrings.t('choose_category'));

    const double cardSpacing = 90.0;
    final double errorBlockHeight = _hasError ? 140.0 : 0.0;

    // Layout calculations for Category vs Browse mode. No upper clamp on
    // row count — the backend can have more than 8 active categories (e.g.
    // via the admin's category-image upload flow), and capping this at 2
    // rows here while still fetching every category left any category
    // beyond the 8th invisible with no way to reach it.
    final int numCatRows =
        _categories.isEmpty ? 1 : ((_categories.length + 3) ~/ 4);
    final double categorySectionHeight = numCatRows * 108.0;
    final double trendingTitleY = 212.0 + categorySectionHeight + 12.0;
    final double trendingCardsStartY = trendingTitleY + 36.0;

    final double startY = _isCategoryView ? 218.0 : trendingCardsStartY;
    final int displayCount = _hasError
        ? 0
        : _isCategoryView
            ? (_isLoading ? 6 : _topics.length)
            : (_isLoading ? 4 : _trendingTopics.length);

    final double emptyStateHeight =
        (_isCategoryView && !_isLoading && !_hasError && _topics.isEmpty)
            ? 160.0
            : 0.0;
    final double lastCardBottom =
        startY + (displayCount * cardSpacing) + errorBlockHeight + emptyStateHeight;
    final double panelHeight = _isCategoryView
        ? (lastCardBottom - 192.0 + 36.0).clamp(180.0, double.infinity)
        : (lastCardBottom - 187.0 + 26.0).clamp(60.0, double.infinity);
    final double adAfterY = lastCardBottom + 12.0;
    final double adTopY = lastCardBottom + 20.0;

    return DesignCanvas(
      adTop: adTopY,
      adAfter: adAfterY,
      bottomNav: displayBottomNav
          ? QuizBottomNav(
              initialIndex: 1,
              onTabSelected: (index) {
                if (index == 2) {
                  Navigator.pushReplacementNamed(context, '/offerwall');
                  return;
                }
                if (index == 0) {
                  Navigator.pushNamedAndRemoveUntil(
                      context, '/', (route) => false);
                } else if (index == 3) {
                  Navigator.pushReplacementNamed(context, '/attendance');
                } else if (index == 4) {
                  Navigator.pushReplacementNamed(context, '/profile');
                }
              },
            )
          : null,
      children: [
        at(0, -139, 412, 467, const PurpleHeader()),
        backButton(context),
        label(
          _isCategoryView ? headingText : AppStrings.t('choose_category'),
          0,
          _isCategoryView ? 122 : 126,
          _isCategoryView ? 36 : 26,
          width: 412,
          align: TextAlign.center,
          color: Colors.white,
          weight: _isCategoryView ? FontWeight.w800 : FontWeight.w700,
        ),
        panel(
          0,
          _isCategoryView ? 192 : 187,
          412,
          panelHeight,
          Colors.white,
          radius: _isCategoryView ? 27 : 31,
        ),

        // ==========================================
        // BROWSE MODE: Dynamic Categories Grid
        // ==========================================
        if (!_isCategoryView) ...[
          // Categories Grid (4 columns, dynamically populated from backend API)
          if (_isLoading && _categories.isEmpty)
            for (var i = 0; i < 4; i++) ...[
              at(
                41.0 + (i * 87.6),
                212.0,
                67.39,
                67.39,
                Container(
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.purple.withValues(alpha: 0.08),
                  ),
                ),
              ),
              at(
                34.0 + (i * 87.6),
                286.0,
                82.0,
                14.0,
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 14),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(7),
                    color: Colors.purple.withValues(alpha: 0.08),
                  ),
                ),
              ),
            ]
          else
            for (var i = 0; i < _categories.length; i++) ...[
              // Icon
              at(
                41.0 + ((i % 4) * 87.6),
                212.0 + ((i ~/ 4) * 108.0),
                67.39,
                67.39,
                CategoryIcon(i % 4, category: _categories[i]),
              ),
              // Category Title
              at(
                34.0 + ((i % 4) * 87.6),
                284.0 + ((i ~/ 4) * 108.0),
                82.0,
                32.0,
                Text(
                  _categories[i].localizedName,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: QuizColors.purple,
                    height: 1.15,
                  ),
                ),
              ),
              // Hit area
              at(
                34.0 + ((i % 4) * 87.6),
                208.0 + ((i ~/ 4) * 108.0),
                82.0,
                112.0,
                GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: () => Navigator.pushNamed(
                    context,
                    '/categories/${_categories[i].id}',
                    arguments: _categories[i],
                  ),
                ),
              ),
            ],

          // "Trending Quizs" Header
          at(
            26,
            trendingTitleY,
            360,
            26,
            Text(
              AppStrings.t('trending_quizzes'),
              style: const TextStyle(
                fontFamily: 'Poppins',
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: QuizColors.purple,
              ),
            ),
          ),
        ],

        // ==========================================
        // ERROR STATE
        // ==========================================
        if (_hasError)
          at(
            24,
            startY,
            364,
            errorBlockHeight,
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.wifi_off_rounded,
                    color: QuizColors.purple, size: 32),
                const SizedBox(height: 10),
                const Text(
                  'Could not load content. Check your connection and try again.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 12.5,
                    color: Color(0xFF757575),
                  ),
                ),
                const SizedBox(height: 12),
                GestureDetector(
                  onTap: _load,
                  child: const Text(
                    'Retry',
                    style: TextStyle(
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

        // ==========================================
        // BROWSE MODE: Trending Quizzes from Backend
        // ==========================================
        if (!_isCategoryView && !_hasError)
          for (var i = 0; i < displayCount; i++)
            at(
              22,
              startY + (i * cardSpacing),
              367,
              80,
              _isLoading
                  ? const QuizCardShimmer()
                  : QuizCard(
                      index: i + 1,
                      title: i < _trendingTopics.length
                          ? _trendingTopics[i].cleanName
                          : null,
                      questionCount: i < _trendingTopics.length
                          ? '${_trendingTopics[i].questionCount} Questions'
                          : null,
                      playedCount: i < _trendingTopics.length
                          ? '${_trendingTopics[i].playedCount} Played'
                          : '${320 + i * 45} Played',
                      progress: i < _trendingTopics.length
                          ? _trendingTopics[i].progress
                          : (0.3 + i * 0.15),
                      onTap: i < _trendingTopics.length
                          ? () async {
                              await Navigator.pushNamed(
                                context,
                                '/question',
                                arguments: _trendingTopics[i],
                              );
                              if (mounted) _load();
                            }
                          : null,
                    ),
            ),

        // ==========================================
        // TOPIC VIEW: Empty State
        // ==========================================
        if (_isCategoryView && !_isLoading && !_hasError && _topics.isEmpty)
          at(
            24,
            startY + 10,
            364,
            200,
            const AnimatedSection(
              delay: Duration(milliseconds: 100),
              child: NoDataView(
                message: 'No quizzes available in this category yet.',
                subMessage: 'Check back soon for new quizzes!',
                imageSize: 105,
              ),
            ),
          ),

        // ==========================================
        // TOPIC VIEW: Quizzes for Selected Category
        // ==========================================
        if (_isCategoryView && !_hasError)
          for (var i = 0; i < displayCount; i++)
            at(
              22,
              startY + (i * cardSpacing),
              367,
              80,
              _isLoading
                  ? const QuizCardShimmer()
                  : QuizCard(
                      index: i + 1,
                      title: i < _topics.length ? _topics[i].cleanName : null,
                      questionCount: i < _topics.length
                          ? '${_topics[i].questionCount} Questions'
                          : null,
                      playedCount: i < _topics.length
                          ? '${_topics[i].playedCount} Played'
                          : '${320 + i * 45} Played',
                      progress: i < _topics.length
                          ? _topics[i].progress
                          : (0.3 + i * 0.15),
                      onTap: i < _topics.length
                          ? () async {
                              await Navigator.pushNamed(
                                context,
                                '/question',
                                arguments: _topics[i],
                              );
                              if (mounted) _load();
                            }
                          : null,
                    ),
            ),
      ],
    );
  }
}
