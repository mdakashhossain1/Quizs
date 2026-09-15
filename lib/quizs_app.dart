import 'package:flutter/material.dart';

import 'screens/achievements_screen.dart';
import 'screens/home_screen.dart';
import 'screens/question_screen.dart';
import 'screens/results_screen.dart';
import 'screens/selection_screen.dart';
import 'widgets/design_widgets.dart';

class QuizsApp extends StatelessWidget {
  const QuizsApp({super.key, this.initialRoute = '/'});

  final String initialRoute;

  @override
  Widget build(BuildContext context) => MaterialApp(
    title: 'Quizs',
    debugShowCheckedModeBanner: false,
    initialRoute: initialRoute,
    theme: ThemeData(
      fontFamily: 'Poppins',
      scaffoldBackgroundColor: const Color(0xFFF8F5FC),
      colorScheme: ColorScheme.fromSeed(seedColor: QuizColors.purple),
      splashFactory: NoSplash.splashFactory,
    ),
    onGenerateRoute: (settings) {
      final screen = switch (settings.name) {
        '/categories' => const SelectionScreen(),
        '/mathematics' => const SelectionScreen(mathematics: true),
        '/achievements' => const AchievementsScreen(),
        '/question' => const QuestionScreen(),
        '/explanation' => const QuestionScreen(explanation: true),
        '/results' => const ResultsScreen(),
        _ => const HomeScreen(),
      };
      return PageRouteBuilder<void>(
        settings: settings,
        transitionDuration: Duration.zero,
        reverseTransitionDuration: Duration.zero,
        pageBuilder: (context, animation, secondaryAnimation) => screen,
      );
    },
  );
}
