import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_svg/flutter_svg.dart';

import '../ads/banner_ad_slot.dart';

abstract final class QuizColors {
  static const purple = Color(0xFF53009C);
  static const darkPurple = Color(0xFF400078);
  static const questionPurple = Color(0xFF5800A4);
  static const green = Color(0xFF10BA65);
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
  });
  final List<Widget> children;
  final Color color;
  final Color topColor;
  final double adTop;
  final double adAfter;
  final double? adBefore;

  @override
  Widget build(BuildContext context) {
    final darkHeader =
        ThemeData.estimateBrightnessForColor(topColor) == Brightness.dark;
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: darkHeader
            ? Brightness.light
            : Brightness.dark,
        statusBarBrightness: darkHeader ? Brightness.dark : Brightness.light,
        systemStatusBarContrastEnforced: false,
        systemNavigationBarColor: QuizColors.darkPurple,
        systemNavigationBarIconBrightness: Brightness.light,
      ),
      child: Scaffold(
        body: Stack(
          children: [
            Positioned(
              top: 0,
              left: 0,
              right: 0,
              height: MediaQuery.paddingOf(context).top,
              child: ColoredBox(color: topColor),
            ),
            SafeArea(
              child: LayoutBuilder(
                builder: (context, constraints) {
                  final width = math.min(412.0, constraints.maxWidth);
                  final scale = width / 412;
                  var bannerTop = math.max(adTop * scale, adAfter * scale + 12);
                  if (adBefore != null) {
                    bannerTop = math.min(
                      bannerTop,
                      adBefore! * scale - BannerAdSlot.height - 12,
                    );
                  }
                  final height = math.max(
                    917 * scale,
                    bannerTop + BannerAdSlot.height + 20,
                  );
                  return Align(
                    alignment: Alignment.topCenter,
                    child: SizedBox(
                      width: width,
                      child: SingleChildScrollView(
                        child: RepaintBoundary(
                          key: const ValueKey('design-canvas'),
                          child: SizedBox(
                            width: width,
                            height: height,
                            child: ColoredBox(
                              color: color,
                              child: Stack(
                                children: [
                                  Positioned(
                                    top: 0,
                                    left: 0,
                                    width: width,
                                    height: 917 * scale,
                                    child: FittedBox(
                                      fit: BoxFit.fill,
                                      child: SizedBox(
                                        width: 412,
                                        height: 917,
                                        child: Stack(
                                          clipBehavior: Clip.hardEdge,
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
                                    child: const BannerAdSlot(),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
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
    label: 'Back',
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
  const CategoryIcon(this.index, {super.key});
  final int index;

  @override
  Widget build(BuildContext context) {
    const backgrounds = [
      Color(0xFFF3D0FF),
      Color(0xFFBCF0FF),
      Color(0xFFCCFFF2),
      Color(0xFFF1FFD0),
    ];
    const edges = [
      Color(0xFFCA00FF),
      Color(0xFF0099FF),
      Color(0xFF00C99A),
      Color(0xFF48E300),
    ];
    const images = [
      '1-2_imgFlask3.png',
      '1-2_imgBook3.png',
      '1-2_imgGlobe1.png',
      '1-2_imgPlanetEarth1.png',
    ];
    const sizes = [60.65, 46.33, 61.0, 53.0];
    const xs = [3.37, 10.95, 2.0, 11.0];
    const ys = [7.58, 14.32, 9.0, 7.0];
    return DecoratedBox(
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: edges[index].withValues(alpha: 0.38),
            blurRadius: 7,
            spreadRadius: 1,
          ),
        ],
      ),
      child: ClipOval(
        child: Stack(
          children: [
            Positioned.fill(child: ColoredBox(color: backgrounds[index])),
            asset(
              images[index],
              xs[index],
              ys[index],
              sizes[index],
              sizes[index],
            ),
            Positioned.fill(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: edges[index], width: 1.2),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

List<Widget> categories(
  BuildContext context,
  double y, {
  bool home = false,
}) => [
  for (var i = 0; i < 4; i++) ...[
    at((home ? 43 : 41) + i * 87.6, y, 67.39, 67.39, CategoryIcon(i)),
    label(
      ['Science', 'Maths', 'Gk', 'Evs'][i],
      (home ? 36 : 34) + i * 87.6,
      y + 74,
      15,
      color: QuizColors.purple,
      weight: FontWeight.w500,
      width: 82,
      align: TextAlign.center,
    ),
    action(
      context,
      '${['Science', 'Maths', 'General knowledge', 'Environmental studies'][i]} quizzes',
      '/mathematics',
      (home ? 36 : 34) + i * 87.6,
      y - 4,
      82,
      105,
    ),
  ],
];

class QuizCard extends StatelessWidget {
  const QuizCard({super.key});

  @override
  Widget build(BuildContext context) => Stack(
    children: [
      panel(0, 0, 367, 80, const Color(0x26BF00FF), radius: 9),
      panel(10, 11, 58, 58, QuizColors.purple, radius: 29),
      label('1', 33, 20, 32, color: Colors.white, weight: FontWeight.w800),
      label('Trigonometry', 78, 13, 20, weight: FontWeight.w800),
      label('10 Questions', 78, 39, 10),
      label(
        '320',
        317,
        29,
        12,
        color: QuizColors.purple,
        weight: FontWeight.w800,
      ),
      label('Played', 315, 43, 8, weight: FontWeight.w300),
      panel(78, 61, 228, 4, Colors.white, radius: 22),
      panel(78, 61, 103, 4, QuizColors.purple, radius: 22),
      action(context, 'Open Trigonometry quiz', '/question', 0, 0, 367, 80),
    ],
  );
}
