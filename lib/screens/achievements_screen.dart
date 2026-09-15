import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../widgets/design_widgets.dart';

class AchievementsScreen extends StatelessWidget {
  const AchievementsScreen({super.key});

  @override
  Widget build(BuildContext context) => DesignCanvas(
    adTop: 836,
    adAfter: 814,
    topColor: Colors.white,
    color: QuizColors.darkPurple,
    children: [
      for (final p in [
        const Offset(68, 300),
        const Offset(-119, 434),
        const Offset(136, 449),
        const Offset(-110, 568),
        const Offset(66, 676),
        const Offset(-121, 810),
      ])
        at(p.dx, p.dy, 420, 184, const OutlineWord()),
      panel(0, -202, 412, 545, Colors.white, radius: 24),
      backButton(context, purple: true),
      label(
        'Achievements',
        149,
        44,
        15,
        color: QuizColors.purple,
        weight: FontWeight.w700,
      ),
      asset('75-2_imgProfile6.png', 155, 106, 101, 101),
      asset('75-2_imgEllipse36.svg', 144, 95, 123, 123),
      asset('75-2_imgEllipse37.svg', 144, 95, 123, 123),
      asset('75-2_imgEllipse38.svg', 257, 156, 9, 9),
      panel(52, 130, 53, 53, QuizColors.purple, radius: 30),
      panel(316, 130, 53, 53, QuizColors.purple, radius: 30),
      label('20', 64, 135, 24, weight: FontWeight.w700, color: Colors.white),
      label('Level', 62, 157, 13, weight: FontWeight.w500, color: Colors.white),
      label('87%', 327, 142, 15, weight: FontWeight.w700, color: Colors.white),
      label(
        'Accuracy',
        323,
        158,
        8,
        weight: FontWeight.w500,
        color: Colors.white,
      ),
      label(
        'Aman Gupta',
        157,
        230,
        15,
        weight: FontWeight.w700,
        color: QuizColors.purple,
      ),
      panel(16, 266, 381, .5, const Color(0xFFD9D9D9)),
      for (var i = 0; i < 4; i++) ...[
        label(
          ['132', '8', '2', '35'][i],
          [59.0, 161.0, 245.0, 323.0][i],
          273,
          24,
          weight: FontWeight.w700,
          color: [
            QuizColors.purple,
            QuizColors.green,
            const Color(0xFFFF0000),
            QuizColors.purple,
          ][i],
          lineHeight: 1.25,
        ),
        label(
          ['Quiz Played', 'Right', 'Wrong', 'This Month'][i],
          [46.0, 155.0, 234.0, 309.0][i],
          303,
          11,
          color: const Color(0x70000000),
          weight: FontWeight.w300,
          lineHeight: 1.25,
        ),
      ],
      for (var i = 0; i < 6; i++) ...[
        at(28, 376 + i * 74, 355, 68, _AchievementRow(index: i)),
        action(
          context,
          'View session ${i + 1} result',
          '/results',
          28,
          376 + i * 74,
          355,
          68,
        ),
      ],
      at(
        40,
        366,
        31.214,
        31.214,
        Transform.rotate(
          angle: -9.83 * math.pi / 180,
          child: const Center(
            child: SizedBox(
              width: 27,
              height: 27,
              child: FigmaAsset('75-2_imgKing1.png'),
            ),
          ),
        ),
      ),
    ],
  );
}

class _AchievementRow extends StatelessWidget {
  const _AchievementRow({required this.index});
  final int index;

  @override
  Widget build(BuildContext context) => Stack(
    children: [
      panel(0, 0, 355, 68, Colors.white, radius: 9),
      panel(15, 16, 35, 35, QuizColors.purple, radius: 20),
      label(
        index == 0 ? '1' : '16',
        index == 0 ? 28 : 21,
        24,
        16,
        color: Colors.white,
        weight: FontWeight.w700,
        lineHeight: 1.25,
      ),
      label(
        index == 0 ? 'st' : 'th',
        index == 0 ? 34 : 37,
        26,
        8,
        color: Colors.white,
        weight: FontWeight.w700,
        lineHeight: 1.265,
      ),
      label(
        'Aman Gupta',
        60,
        13,
        15,
        color: QuizColors.purple,
        weight: FontWeight.w700,
        lineHeight: 1.25,
      ),
      label(
        'Session ${[5, 4, 3, 4, 3, 3][index]}',
        168,
        17,
        10,
        color: QuizColors.purple,
        weight: FontWeight.w700,
        lineHeight: 1.25,
      ),
      label(
        'Quizs :',
        60,
        38,
        10,
        color: const Color(0xFF868686),
        weight: FontWeight.w700,
        lineHeight: 1.25,
      ),
      label(
        'Maths',
        96,
        38,
        10,
        color: QuizColors.purple,
        weight: FontWeight.w700,
        lineHeight: 1.25,
      ),
      label(
        '100%',
        306,
        25,
        15,
        color: QuizColors.purple,
        weight: FontWeight.w700,
      ),
      label(
        'Accuracy',
        306,
        41,
        8,
        color: QuizColors.purple,
        weight: FontWeight.w500,
      ),
    ],
  );
}
