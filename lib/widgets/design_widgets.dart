import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_svg/flutter_svg.dart';

import '../ads/banner_ad_slot.dart';
import '../l10n/app_strings.dart';
import '../models/question_model.dart';
import '../services/quiz_api_service.dart';

abstract final class QuizColors {
  static const purple = Color(0xFF53009C);
  static const darkPurple = Color(0xFF400078);
  static const questionPurple = Color(0xFF5800A4);
  static const green = Color(0xFF10BA65);
}

/// Today's daily-target completion (roadmap §8.1 / leaderboard-achievement
/// roadmap §12) — fills clockwise from the top as [progress] (0.0-1.0,
/// already capped server-side) approaches 1.0. Shared by the Profile and
/// Achievement screens so the same ring never gets reimplemented twice.
class DailyProgressRing extends StatelessWidget {
  const DailyProgressRing({super.key, required this.progress, this.size = 123});

  final double progress;
  final double size;

  @override
  Widget build(BuildContext context) => CustomPaint(
        size: Size(size, size),
        painter: _DailyProgressRingPainter(progress: progress.clamp(0.0, 1.0)),
      );
}

class _DailyProgressRingPainter extends CustomPainter {
  _DailyProgressRingPainter({required this.progress});

  final double progress;
  static const double _strokeWidth = 7;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = (size.shortestSide - _strokeWidth) / 2;

    final track = Paint()
      ..color = const Color(0xFFE9DFF5)
      ..style = PaintingStyle.stroke
      ..strokeWidth = _strokeWidth
      ..strokeCap = StrokeCap.round;
    canvas.drawCircle(center, radius, track);

    if (progress <= 0) return;

    final fill = Paint()
      ..color = const Color(0xFF00C853)
      ..style = PaintingStyle.stroke
      ..strokeWidth = _strokeWidth
      ..strokeCap = StrokeCap.round;
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      -math.pi / 2,
      2 * math.pi * progress,
      false,
      fill,
    );
  }

  @override
  bool shouldRepaint(covariant _DailyProgressRingPainter oldDelegate) =>
      oldDelegate.progress != progress;
}

class DesignCanvas extends StatelessWidget {
  const DesignCanvas({
    super.key,
    required this.children,
    this.color = Colors.white,
    this.topColor = QuizColors.darkPurple,
    this.adTop = 820,
    this.adAfter = 0,
    this.adBefore,
    this.darkBanner,
    this.bottomNav,
  });
  final List<Widget> children;
  final Color color;
  final Color topColor;
  final double adTop;
  final double adAfter;
  final double? adBefore;
  final bool? darkBanner;
  final Widget? bottomNav;

  @override
  Widget build(BuildContext context) {
    final darkHeader =
        ThemeData.estimateBrightnessForColor(topColor) == Brightness.dark;
    return ListenableBuilder(
      listenable: AppLanguage.instance,
      builder: (context, _) => AnnotatedRegion<SystemUiOverlayStyle>(
        value: SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: darkHeader
              ? Brightness.light
              : Brightness.dark,
          statusBarBrightness: darkHeader ? Brightness.dark : Brightness.light,
          systemStatusBarContrastEnforced: false,
          systemNavigationBarColor: Colors.transparent,
          systemNavigationBarIconBrightness: darkHeader
              ? Brightness.light
              : Brightness.dark,
          systemNavigationBarContrastEnforced: false,
        ),
        child: Scaffold(
          extendBodyBehindAppBar: true,
          extendBody: true,
          backgroundColor: color,
          body: SafeArea(
            top: false,
            child: LayoutBuilder(
              builder: (context, constraints) {
                final width = math.min(412.0, constraints.maxWidth);
              final scale = width / 412;
              final viewportHeight = constraints.maxHeight;
              var bannerTop = math.max(adTop * scale, adAfter * scale + 12);
              if (adBefore != null) {
                bannerTop = math.min(
                  bannerTop,
                  adBefore! * scale - BannerAdSlot.height - 12,
                );
              }
              final virtualCanvasHeight =
                  math.max(917.0, (adAfter > 0 ? (adAfter + 80.0) : 917.0));
              final contentHeight = math.max(
                virtualCanvasHeight * scale,
                bannerTop + BannerAdSlot.height + 20,
              );
              final canvasHeight = math.max(contentHeight, viewportHeight);

              return Align(
                alignment: Alignment.topCenter,
                child: SizedBox(
                  width: width,
                  height: canvasHeight,
                  child: RepaintBoundary(
                    key: const ValueKey('design-canvas'),
                    child: Stack(
                      children: [
                        Positioned.fill(
                          child: SingleChildScrollView(
                            padding: EdgeInsets.only(
                              bottom: bottomNav != null ? (76 * scale) : 0,
                            ),
                            child: SizedBox(
                              width: width,
                              height: contentHeight,
                              child: ColoredBox(
                                color: color,
                                child: Stack(
                                  children: [
                                    Positioned(
                                      top: 0,
                                      left: 0,
                                      width: width,
                                      height: virtualCanvasHeight * scale,
                                      child: FittedBox(
                                        fit: BoxFit.fill,
                                        child: SizedBox(
                                          width: 412,
                                          height: virtualCanvasHeight,
                                          child: Stack(
                                            clipBehavior: Clip.none,
                                            children: children,
                                          ),
                                        ),
                                      ),
                                    ),
                                    Positioned(
                                      left: 0,
                                      right: 0,
                                      top: bannerTop,
                                      height: BannerAdSlot.height,
                                      child: BannerAdSlot(
                                        isDark: darkBanner ??
                                            (color == QuizColors.darkPurple ||
                                                topColor ==
                                                    const Color(0xFF31005C) ||
                                                ThemeData
                                                        .estimateBrightnessForColor(
                                                          color,
                                                        ) ==
                                                    Brightness.dark),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                        if (bottomNav != null)
                          Positioned(
                            left: 0,
                            right: 0,
                            bottom: math.max(14.0, 18.0 * scale),
                            child: Align(
                              alignment: Alignment.bottomCenter,
                              child: SizedBox(
                                width: 384 * scale,
                                child: FittedBox(
                                  fit: BoxFit.scaleDown,
                                  child: bottomNav!,
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ),
    ),
  );
}
}

Widget at(double x, double y, double width, double height, Widget child) =>
    Positioned(left: x, top: y, width: width, height: height, child: child);

Widget panel(
  double x,
  double y,
  double width,
  double height,
  Color color, {
  double radius = 0,
  Color? border,
  double borderWidth = 1,
}) => at(
  x,
  y,
  width,
  height,
  DecoratedBox(
    decoration: BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(radius),
      border: border == null
          ? null
          : Border.all(color: border, width: borderWidth),
    ),
  ),
);

Widget label(
  String text,
  double x,
  double y,
  double size, {
  double? width,
  double? height,
  Color color = Colors.black,
  FontWeight weight = FontWeight.w400,
  String family = 'Poppins',
  double lineHeight = 1.5,
  TextAlign align = TextAlign.left,
}) => Positioned(
  left: x,
  top: y,
  width: width,
  height: height,
  child: Text(
    text,
    textAlign: align,
    softWrap: width != null,
    style: TextStyle(
      fontFamily: family,
      fontSize: size,
      fontWeight: weight,
      height: lineHeight,
      color: color,
      letterSpacing: 0,
    ),
  ),
);

class FigmaAsset extends StatelessWidget {
  const FigmaAsset(this.name, {super.key, this.color, this.fit = BoxFit.fill});
  final String name;
  final Color? color;
  final BoxFit fit;

  @override
  Widget build(BuildContext context) {
    final path = 'assets/figma/$name';
    return name.endsWith('.svg')
        ? SvgPicture.asset(
            path,
            fit: fit,
            colorFilter: color == null
                ? null
                : ColorFilter.mode(color!, BlendMode.srcIn),
          )
        : Image.asset(
            path,
            fit: fit,
            color: color,
            colorBlendMode: BlendMode.srcIn,
            filterQuality: FilterQuality.medium,
            excludeFromSemantics: true,
          );
  }
}

Widget asset(
  String name,
  double x,
  double y,
  double width,
  double height, {
  Color? color,
}) => at(x, y, width, height, FigmaAsset(name, color: color));

class DesignAction extends StatelessWidget {
  const DesignAction({
    super.key,
    required this.label,
    required this.onTap,
    this.child,
  });
  final String label;
  final VoidCallback onTap;
  final Widget? child;

  @override
  Widget build(BuildContext context) => Semantics(
    button: true,
    label: label,
    child: Tooltip(
      message: label,
      child: InkWell(onTap: onTap, child: child ?? const SizedBox.expand()),
    ),
  );
}

Widget action(
  BuildContext context,
  String name,
  String route,
  double x,
  double y,
  double width,
  double height,
) => at(
  x,
  y,
  width,
  height,
  DesignAction(
    label: name,
    onTap: () => Navigator.of(context).pushNamed(route),
  ),
);

void goHome(BuildContext context) =>
    Navigator.of(context).pushNamedAndRemoveUntil('/', (_) => false);

Widget backButton(
  BuildContext context, {
  bool compact = false,
  bool purple = false,
}) => at(
  24,
  26,
  48,
  48,
  DesignAction(
    label: AppStrings.t('back'),
    onTap: () {
      if (Navigator.of(context).canPop()) {
        Navigator.of(context).pop();
      } else {
        goHome(context);
      }
    },
    child: Stack(
      children: [
        panel(
          compact ? 8 : 4,
          compact ? 7 : 4,
          compact ? 27 : 40,
          compact ? 27 : 40,
          compact
              ? const Color(0x5EFFFFFF)
              : purple
              ? QuizColors.purple
              : Colors.white,
          radius: compact ? 6 : 30,
        ),
        asset(
          '11-222_imgLeftArrow1.png',
          compact ? 11 : 14,
          compact ? 11 : 14,
          19,
          19,
          color: compact || purple ? Colors.white : Colors.black,
        ),
      ],
    ),
  ),
);

class OutlineWord extends StatelessWidget {
  const OutlineWord({
    super.key,
    this.text = 'Quizs',
    this.size = 128,
    this.color = const Color(0x15FFFFFF),
    this.family = 'Quizlo',
  });
  final String text;
  final double size;
  final Color color;
  final String family;

  @override
  Widget build(BuildContext context) => Text(
    text,
    softWrap: false,
    style: TextStyle(
      fontFamily: family,
      fontSize: size,
      height: family == 'HappySchool' ? 1.192 : 1.44,
      foreground: Paint()
        ..style = PaintingStyle.stroke
        ..strokeWidth = 1
        ..color = color,
    ),
  );
}

class PurpleHeader extends StatelessWidget {
  const PurpleHeader({super.key, this.home = false});
  final bool home;

  @override
  Widget build(BuildContext context) => ClipRRect(
    borderRadius: BorderRadius.circular(22),
    child: ColoredBox(
      color: QuizColors.darkPurple,
      child: Stack(
        clipBehavior: Clip.hardEdge,
        children: [
          asset('1-2_imgMaskGroup.svg', 0, 0, 412, 467),
          asset('1-2_imgEllipse6.svg', 188, home ? -235 : -185, 555, 564),
          for (final position in [
            Offset(138, home ? 14 : 64),
            Offset(-257, home ? 102 : 152),
            const Offset(232, 311),
            Offset(138, home ? 147 : 197),
            const Offset(-159, 278),
          ])
            at(position.dx, position.dy, 420, 184, const OutlineWord()),
        ],
      ),
    ),
  );
}

class QuestionBackground extends StatelessWidget {
  const QuestionBackground({super.key, this.results = false});
  final bool results;

  @override
  Widget build(BuildContext context) {
    final ellipseY = results ? -215.0 : -161.0;
    return Stack(
      clipBehavior: Clip.hardEdge,
      children: [
        const Positioned.fill(
          child: ColoredBox(color: QuizColors.questionPurple),
        ),
        if (!results)
          at(
            -213,
            198,
            795,
            795,
            Opacity(
              opacity: 0.5,
              child: ColorFiltered(
                colorFilter: const ColorFilter.mode(
                  QuizColors.questionPurple,
                  BlendMode.color,
                ),
                child: const FigmaAsset(
                  '7-63_img73274TeachingSchoolElementsLearningDownloadHqPng1.png',
                ),
              ),
            ),
          ),
        at(
          -92,
          ellipseY,
          595,
          495,
          ClipOval(
            child: ColoredBox(
              color: const Color(0xFF31005C),
              child: Stack(
                clipBehavior: Clip.hardEdge,
                children: [
                  for (final p in [
                    const Offset(-232, -59),
                    const Offset(225, 172),
                    const Offset(152, 0),
                    const Offset(-168, 130),
                  ])
                    at(
                      p.dx + 92,
                      p.dy - ellipseY,
                      420,
                      184,
                      const OutlineWord(color: Color(0x29FFFFFF)),
                    ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class CategoryIcon extends StatelessWidget {
  const CategoryIcon(this.index, {super.key, this.category});
  final int index;
  final QuizCategory? category;

  Color _parseColor(String? hex, Color fallback) {
    if (hex == null) return fallback;
    try {
      final h = hex.replaceAll('#', '').trim();
      if (h.length == 6) return Color(int.parse('FF$h', radix: 16));
      if (h.length == 8) return Color(int.parse(h, radix: 16));
    } catch (_) {}
    return fallback;
  }

  /// Single generic placeholder for any category without an admin-uploaded
  /// image — never a per-category/slug mapping
  /// (dynamic_quiz_category_images_brd.md §7).
  static const _genericFallbackImage = '1-2_imgBook3.png';

  @override
  Widget build(BuildContext context) {
    const defaultBackgrounds = [
      Color(0xFFF3D0FF),
      Color(0xFFBCF0FF),
      Color(0xFFCCFFF2),
      Color(0xFFF1FFD0),
    ];
    const defaultEdges = [
      Color(0xFFCA00FF),
      Color(0xFF0099FF),
      Color(0xFF00C99A),
      Color(0xFF48E300),
    ];

    final idx = index % 4;
    final edgeColor = category?.color != null
        ? _parseColor(category!.color, defaultEdges[idx])
        : defaultEdges[idx];
    final bgColor = category?.color != null
        ? edgeColor.withValues(alpha: 0.18)
        : defaultBackgrounds[idx];
    final imageUrl = category?.imageUrl;

    return DecoratedBox(
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: edgeColor.withValues(alpha: 0.35),
            blurRadius: 7,
            spreadRadius: 1,
          ),
        ],
      ),
      child: ClipOval(
        child: Stack(
          children: [
            Positioned.fill(child: ColoredBox(color: bgColor)),
            if (imageUrl != null && imageUrl.isNotEmpty)
              Positioned.fill(
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => const Padding(
                    padding: EdgeInsets.all(14),
                    child: FigmaAsset(_genericFallbackImage, fit: BoxFit.contain),
                  ),
                ),
              )
            else
              const Padding(
                padding: EdgeInsets.all(14),
                child: FigmaAsset(_genericFallbackImage, fit: BoxFit.contain),
              ),
            Positioned.fill(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: edgeColor, width: 1.3),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class HomeCategoriesRow extends StatelessWidget {
  const HomeCategoriesRow({super.key});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: AppLanguage.instance,
      builder: (context, _) => FutureBuilder<List<QuizCategory>>(
        future: QuizApiService.instance.getCategories(),
        builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: List.generate(
              4,
              (i) => Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 67.39,
                    height: 67.39,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.purple.withValues(alpha: 0.08),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    width: 55,
                    height: 12,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(6),
                      color: Colors.purple.withValues(alpha: 0.08),
                    ),
                  ),
                ],
              ),
            ),
          );
        }

        final cats = snapshot.data ?? const [];
        if (cats.isEmpty) {
          return const SizedBox.shrink();
        }

        final displayCats = cats.take(4).toList();
        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            for (var i = 0; i < displayCats.length; i++)
              GestureDetector(
                onTap: () => Navigator.pushNamed(
                  context,
                  '/categories/${displayCats[i].id}',
                  arguments: displayCats[i],
                ),
                child: SizedBox(
                  width: 82,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      SizedBox(
                        width: 67.39,
                        height: 67.39,
                        child: CategoryIcon(i % 4, category: displayCats[i]),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        displayCats[i].localizedName,
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
                    ],
                  ),
                ),
              ),
          ],
        );
      },
    ),
  );
  }
}

class QuizCard extends StatelessWidget {
  const QuizCard({
    super.key,
    this.index = 1,
    this.title,
    this.questionCount,
    this.playedCount = '320',
    this.progress = 0.45,
    this.onTap,
  });

  final int index;
  final String? title;
  final String? questionCount;
  final String playedCount;
  final double progress;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final displayTitle = title ?? AppStrings.t('trigonometry');
    final displayCount = questionCount ?? AppStrings.t('questions_count');
    final progressWidth = (228 * progress.clamp(0.0, 1.0));

    return Stack(
      children: [
        panel(0, 0, 367, 80, const Color(0x26BF00FF), radius: 9),
        panel(10, 11, 58, 58, QuizColors.purple, radius: 29),
        at(
          10,
          11,
          58,
          58,
          Center(
            child: Text(
              '$index',
              style: const TextStyle(
                fontFamily: 'Poppins',
                fontSize: 26,
                fontWeight: FontWeight.w800,
                color: Colors.white,
                height: 1.0,
              ),
            ),
          ),
        ),
        at(
          78,
          13,
          228,
          26,
          Text(
            displayTitle,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontFamily: 'Poppins',
              fontSize: displayTitle.length > 25
                  ? 14.0
                  : (displayTitle.length > 18 ? 15.0 : 16.5),
              fontWeight: FontWeight.w800,
              color: Colors.black,
              height: 1.15,
            ),
          ),
        ),
        label(displayCount, 78, 39, 10),
        Positioned(
          left: 308,
          top: 26,
          width: 52,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Text(
                playedCount,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: QuizColors.purple,
                  height: 1.1,
                ),
              ),
              Text(
                AppStrings.t('played_count'),
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 8,
                  fontWeight: FontWeight.w300,
                  color: Colors.black,
                  height: 1.1,
                ),
              ),
            ],
          ),
        ),
        panel(78, 61, 228, 4, Colors.white, radius: 22),
        panel(78, 61, progressWidth, 4, QuizColors.purple, radius: 22),
        if (index == 1)
          at(
            0,
            0,
            367,
            80,
            DesignAction(
              label: AppStrings.t('open_trigo'),
              onTap: onTap ?? () => Navigator.of(context).pushNamed('/question'),
            ),
          )
        else
          at(
            0,
            0,
            367,
            80,
            DesignAction(
              label: 'Open $displayTitle',
              onTap: onTap ?? () => Navigator.of(context).pushNamed('/question'),
            ),
          ),
      ],
    );
  }
}

class AnimatedSection extends StatefulWidget {
  const AnimatedSection({
    super.key,
    required this.child,
    this.delay = Duration.zero,
    this.duration = const Duration(milliseconds: 320),
    this.offset = const Offset(0, 14),
  });

  final Widget child;
  final Duration delay;
  final Duration duration;
  final Offset offset;

  @override
  State<AnimatedSection> createState() => _AnimatedSectionState();
}

class _AnimatedSectionState extends State<AnimatedSection>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _fade;
  late final Animation<Offset> _slide;
  Timer? _delayTimer;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: widget.duration,
    );
    _fade = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOutCubic,
    );
    _slide = Tween<Offset>(
      begin: widget.offset,
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOutCubic,
    ));

    if (widget.delay == Duration.zero) {
      _controller.forward();
    } else {
      _delayTimer = Timer(widget.delay, () {
        if (mounted) _controller.forward();
      });
    }
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (MediaQuery.disableAnimationsOf(context)) {
      _delayTimer?.cancel();
      _delayTimer = null;
      _controller.value = 1.0;
    }
  }

  @override
  void dispose() {
    _delayTimer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: _controller,
    builder: (context, child) => Opacity(
      opacity: _fade.value.clamp(0.0, 1.0),
      child: Transform.translate(
        offset: _slide.value,
        child: child,
      ),
    ),
    child: widget.child,
  );
}
