import 'package:flutter/material.dart';

import 'l10n/app_strings.dart';
import 'models/question_model.dart';
import 'screens/achievements_screen.dart';
import 'screens/home_screen.dart';

import 'screens/edit_profile_screen.dart';
import 'screens/forgot_password_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/question_screen.dart';
import 'screens/results_screen.dart';
import 'screens/selection_screen.dart';
import 'screens/signin_screen.dart';
import 'screens/signup_screen.dart';
import 'screens/verify_code_screen.dart';
import 'services/auth_service.dart';
import 'widgets/design_widgets.dart';

class QuizsApp extends StatelessWidget {
  const QuizsApp({super.key, this.initialRoute});

  final String? initialRoute;

  @override
  Widget build(BuildContext context) {
    final effectiveInitialRoute = initialRoute ??
        (AuthService.instance.isLoggedIn ? '/' : '/signin');

    return AnimatedBuilder(
      animation: Listenable.merge([AppLanguage.instance, AuthService.instance]),
      builder: (context, _) => MaterialApp(
        title: 'Quizs',
        debugShowCheckedModeBanner: false,
        initialRoute: effectiveInitialRoute,
        theme: ThemeData(
          fontFamily: 'Poppins',
          scaffoldBackgroundColor: const Color(0xFFF8F5FC),
          colorScheme: ColorScheme.fromSeed(seedColor: QuizColors.purple),
          splashFactory: NoSplash.splashFactory,
        ),
        onGenerateRoute: (settings) {
          final target = settings.name ?? '/';
          final screen = switch (target) {
            '/' => AuthService.instance.isLoggedIn
                ? const HomeScreen()
                : const SignInScreen(),
            '/dashboard' => const SelectionScreen(showBottomNav: true),
            '/notifications' => const NotificationsScreen(),
            '/categories' => const SelectionScreen(showBottomNav: true),

            '/mathematics' => const SelectionScreen(mathematics: true, categoryKey: 'math'),
            '/science' => SelectionScreen(categoryKey: 'science', title: AppStrings.t('science')),
            '/gk' => SelectionScreen(categoryKey: 'gk', title: AppStrings.t('gk')),
            '/profile' => const ProfileScreen(),
            '/edit-profile' => const EditProfileScreen(),
            '/achievements' => const AchievementsScreen(),
            '/signup' => const SignUpScreen(),
            '/signin' || '/login' => const SignInScreen(),
            '/forgot-password' => const ForgotPasswordScreen(),
            '/verify' || '/verify-code' => VerifyCodeScreen(
                email: settings.arguments is VerifyCodeArgs
                    ? (settings.arguments as VerifyCodeArgs).email
                    : (settings.arguments is String ? settings.arguments as String : null),
                isPasswordReset: settings.arguments is VerifyCodeArgs &&
                    (settings.arguments as VerifyCodeArgs).isPasswordReset,
              ),
            '/question' => QuestionScreen(
                topic: settings.arguments is QuizTopic ? settings.arguments as QuizTopic : null,
              ),
            '/explanation' => QuestionScreen(
                explanation: true,
                topic: settings.arguments is QuizTopic ? settings.arguments as QuizTopic : null,
              ),
            '/results' => ResultsScreen(
                resultArgs: settings.arguments is QuizResultArgs
                    ? settings.arguments as QuizResultArgs
                    : null,
              ),
            _ => AuthService.instance.isLoggedIn
                ? const HomeScreen()
                : const SignInScreen(),
          };
          final name = settings.name ?? '/';
          final isTabRoute = name == '/' ||
              name == '/categories' ||
              name == '/dashboard' ||
              name == '/profile';

          if (isTabRoute) {
            return PageRouteBuilder<void>(
              settings: settings,
              transitionDuration: Duration.zero,
              reverseTransitionDuration: Duration.zero,
              pageBuilder: (context, _, _) => screen,
            );
          }

          return MaterialPageRoute<void>(
            settings: settings,
            builder: (context) => screen,
          );
        },
      ),
    );
  }
}
