import 'package:flutter/material.dart';

import 'l10n/app_strings.dart';
import 'models/question_model.dart';
import 'screens/achievements_screen.dart';
import 'screens/attendance_screen.dart';
import 'screens/home_screen.dart';

import 'screens/edit_profile_screen.dart';
import 'screens/force_change_password_screen.dart';
import 'screens/forgot_password_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/offerwall_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/question_screen.dart';
import 'screens/results_screen.dart';
import 'screens/selection_screen.dart';
import 'screens/signin_screen.dart';
import 'screens/signup_screen.dart';
import 'screens/verify_code_screen.dart';
import 'services/auth_service.dart';
import 'services/push_notification_service.dart';
import 'widgets/design_widgets.dart';

class QuizsApp extends StatelessWidget {
  const QuizsApp({super.key, this.initialRoute});

  final String? initialRoute;

  /// `/` and any unrecognized route fall back to this: signed out -> sign in,
  /// signed in with a temp password still pending -> force the change first,
  /// otherwise -> home.
  static String _gateRoute() {
    if (!AuthService.instance.isLoggedIn) return '/signin';
    if (AuthService.instance.mustChangePassword) return '/force-change-password';
    return '/';
  }

  static Widget _gateScreen() {
    if (!AuthService.instance.isLoggedIn) return const SignInScreen();
    if (AuthService.instance.mustChangePassword) return const ForceChangePasswordScreen();
    return const HomeScreen();
  }

  @override
  Widget build(BuildContext context) {
    final effectiveInitialRoute = initialRoute ?? _gateRoute();

    return AnimatedBuilder(
      animation: Listenable.merge([AppLanguage.instance, AuthService.instance]),
      builder: (context, _) => MaterialApp(
        title: 'Quizs',
        debugShowCheckedModeBanner: false,
        navigatorKey: PushNotificationService.navigatorKey,
        initialRoute: effectiveInitialRoute,
        theme: ThemeData(
          fontFamily: 'Poppins',
          scaffoldBackgroundColor: const Color(0xFFF8F5FC),
          colorScheme: ColorScheme.fromSeed(seedColor: QuizColors.purple),
          splashFactory: NoSplash.splashFactory,
        ),
        onGenerateRoute: (settings) {
          final target = settings.name ?? '/';

          if (target.startsWith('/categories/')) {
            final idStr = target.substring('/categories/'.length);
            final catId = int.tryParse(idStr);
            if (catId != null) {
              final title = settings.arguments is QuizCategory
                  ? (settings.arguments as QuizCategory).name
                  : null;
              return MaterialPageRoute<void>(
                settings: settings,
                builder: (_) => SelectionScreen(categoryId: catId, title: title),
              );
            }
          }

          final screen = switch (target) {
            '/' => _gateScreen(),
            '/dashboard' => const SelectionScreen(showBottomNav: true),
            '/notifications' => const NotificationsScreen(),
            '/offerwall' => const OfferwallScreen(),
            '/categories' => settings.arguments is QuizCategory
                ? SelectionScreen(
                    categoryId: (settings.arguments as QuizCategory).id,
                    title: (settings.arguments as QuizCategory).name,
                  )
                : const SelectionScreen(showBottomNav: true),

            '/mathematics' => SelectionScreen(
                categorySlug: 'mathematics-logic',
                title: AppStrings.t('maths'),
              ),
            '/science' => SelectionScreen(
                categorySlug: 'science-nature',
                title: AppStrings.t('science'),
              ),
            '/gk' => SelectionScreen(
                categorySlug: 'general-knowledge',
                title: AppStrings.t('gk'),
              ),
            '/profile' => const ProfileScreen(),
            '/attendance' => const AttendanceScreen(),
            '/edit-profile' => const EditProfileScreen(),
            '/achievements' => const AchievementsScreen(),
            '/signup' => const SignUpScreen(),
            '/signin' || '/login' => const SignInScreen(),
            '/force-change-password' => const ForceChangePasswordScreen(),
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
            _ => _gateScreen(),
          };
          final name = settings.name ?? '/';
          final isTabRoute = name == '/' ||
              name == '/categories' ||
              name == '/dashboard' ||
              name == '/profile' ||
              name == '/offerwall' ||
              name == '/attendance';

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
