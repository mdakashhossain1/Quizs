import 'package:flutter/material.dart';

import '../widgets/design_widgets.dart';

class SelectionScreen extends StatelessWidget {
  const SelectionScreen({super.key, this.mathematics = false});
  final bool mathematics;

  @override
  Widget build(BuildContext context) => DesignCanvas(
    adTop: mathematics ? 833 : 868,
    adAfter: mathematics ? 759 : 856,
    children: [
      at(0, -139, 412, 467, const PurpleHeader()),
      if (!mathematics) backButton(context),
      label(
        mathematics ? 'Mathematics' : 'Choose category',
        0,
        mathematics ? 122 : 130,
        mathematics ? 36 : 24,
        width: 412,
        align: TextAlign.center,
        color: Colors.white,
        weight: mathematics ? FontWeight.w800 : FontWeight.w600,
      ),
      panel(
        0,
        mathematics ? 192 : 187,
        412,
        745,
        Colors.white,
        radius: mathematics ? 27 : 31,
      ),
      if (!mathematics) ...[
        ...categories(context, 226),
        ...categories(context, 340),
        label(
          'Trending Quizs',
          32,
          465,
          20,
          color: QuizColors.purple,
          weight: FontWeight.w800,
        ),
      ],
      if (mathematics)
        for (final y in [218.0, 311.0, 403.0, 495.0, 587.0, 679.0])
          at(22, y, 367, 80, const QuizCard())
      else
        for (final y in [504.0, 596.0, 688.0, 776.0])
          at(22, y, 367, 80, const QuizCard()),
    ],
  );
}
