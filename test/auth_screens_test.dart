import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:quizs/l10n/app_strings.dart';
import 'package:quizs/quizs_app.dart';
import 'package:quizs/services/api_client.dart';
import 'package:quizs/services/auth_service.dart';

import 'support/fake_secure_storage.dart';

/// Stubs the Laravel auth endpoints so widget tests exercise the real
/// request/response path without needing a live backend.
final _mockAuthClient = MockClient((request) async {
  Map<String, dynamic> body = {};
  if (request.body.isNotEmpty) {
    body = jsonDecode(request.body) as Map<String, dynamic>;
  }

  if (request.url.path.endsWith('/auth/login') ||
      request.url.path.endsWith('/auth/google-login') ||
      request.url.path.endsWith('/auth/verify-otp') ||
      request.url.path.endsWith('/auth/reset-password')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'token': 'test-token',
        'user': {
          'name': body['name'] ?? 'Aman Gupta',
          'email': body['email'] ?? 'amangupta@gmail.com',
          'avatar': body['avatar'],
        },
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/auth/register')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'message': 'Registration successful. A verification code has been sent to your email.',
        'email': body['email'],
      }),
      201,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/auth/send-otp') ||
      request.url.path.endsWith('/auth/forgot-password')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'message': 'A new verification code has been sent to your email.',
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/auth/update-profile')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'message': 'Profile updated successfully.',
        'user': {
          'name': body['name'],
          'email': body['email'] ?? 'amangupta@gmail.com',
          'avatar': body['avatar'],
        },
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/auth/change-password')) {
    return http.Response(
      jsonEncode({'success': true, 'message': 'Password changed successfully.'}),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/auth/logout')) {
    return http.Response(
      jsonEncode({'success': true}),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  return http.Response(jsonEncode({'success': false}), 404);
});

void main() {
  setUp(() async {
    installFakeSecureStorage();
    SharedPreferences.setMockInitialValues({});
    TestWidgetsFlutterBinding.ensureInitialized()
        .platformDispatcher
        .accessibilityFeaturesTestValue = const FakeAccessibilityFeatures(
      disableAnimations: true,
    );
    AppLanguage.instance.setLanguage(AppLanguageType.english);
    ApiClient.instance.debugClient = _mockAuthClient;
    await AuthService.instance.logout();
  });

  tearDown(() {
    TestWidgetsFlutterBinding.ensureInitialized()
        .platformDispatcher
        .clearAccessibilityFeaturesTestValue();
  });

  group('Auth Persistence & Screens Tests', () {
    testWidgets('Unauthenticated user starts at SignInScreen by default', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await AuthService.instance.logout();
      expect(AuthService.instance.isLoggedIn, isFalse);

      await tester.pumpWidget(const QuizsApp());
      await tester.pumpAndSettle();

      expect(find.text('Welcome Back'), findsOneWidget);
      expect(find.text('Sign in'), findsOneWidget);
    });

    testWidgets('SignInScreen logs in and navigates to HomeScreen', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/signin'));
      await tester.pumpAndSettle();

      expect(find.text('Welcome Back'), findsOneWidget);

      // Enter email and password
      await tester.enterText(find.byType(TextField).at(0), 'amangupta@gmail.com');
      await tester.enterText(find.byType(TextField).at(1), 'password123');

      // Tap Sign in
      await tester.tap(find.text('Sign in'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(AuthService.instance.isLoggedIn, isTrue);
      expect(find.text('Quiz Category'), findsOneWidget);
    });

    testWidgets('SignUpScreen renders correctly and navigates to VerifyCodeScreen', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/signup'));
      await tester.pumpAndSettle();

      expect(find.text('Create Account'), findsOneWidget);
      expect(find.text('Name'), findsOneWidget);
      expect(find.text('Email'), findsOneWidget);
      expect(find.text('Password'), findsOneWidget);
      expect(find.textContaining('Terms & Condition'), findsOneWidget);
      expect(find.text('Sign up'), findsOneWidget);
      expect(find.text('Continue with Google'), findsOneWidget);
      expect(find.text('Already have an account?'), findsOneWidget);
      expect(find.text('Sign In'), findsOneWidget);

      // Enter name, email, password
      await tester.enterText(find.byType(TextField).at(0), 'Aman Gupta');
      await tester.enterText(find.byType(TextField).at(1), 'amangupta@gmail.com');
      await tester.enterText(find.byType(TextField).at(2), 'password123');

      // Tap Sign up to navigate to Verify Code
      await tester.tap(find.text('Sign up'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(find.text('Verify Code'), findsOneWidget);
    });

    testWidgets('ForgotPasswordScreen requests a reset code, then VerifyCodeScreen resets the password and logs in', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/forgot-password'));
      await tester.pumpAndSettle();

      expect(find.text('Forgot Password?'), findsOneWidget);
      expect(find.text('Email'), findsOneWidget);
      expect(find.text('Send Reset Code'), findsOneWidget);
      expect(find.text('Remember your password?'), findsOneWidget);
      expect(find.text('Sign In'), findsOneWidget);

      // Enter email, then tap Send Reset Code
      await tester.enterText(find.byType(TextField), 'amangupta@gmail.com');
      await tester.tap(find.text('Send Reset Code'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(find.text('Verify Code'), findsOneWidget);
      expect(AuthService.instance.isLoggedIn, isFalse);

      // Reset mode shows the new/confirm password fields alongside the OTP boxes.
      expect(find.text('New Password'), findsOneWidget);
      expect(find.text('Confirm Password'), findsOneWidget);
      expect(find.text('Reset Password'), findsOneWidget);

      final pinFields = find.byType(TextField);
      await tester.enterText(pinFields.at(0), '1');
      await tester.enterText(pinFields.at(1), '2');
      await tester.enterText(pinFields.at(2), '3');
      await tester.enterText(pinFields.at(3), '4');
      await tester.enterText(pinFields.at(4), 'newpassword1');
      await tester.enterText(pinFields.at(5), 'newpassword1');

      await tester.tap(find.text('Reset Password'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(AuthService.instance.isLoggedIn, isTrue);
      expect(find.text('Quiz Category'), findsOneWidget);
    });

    testWidgets('Sign up registers via the API, then VerifyCodeScreen verifies OTP and persists login to HomeScreen', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/signup'));
      await tester.pumpAndSettle();

      await tester.enterText(find.byType(TextField).at(0), 'Aman Gupta');
      await tester.enterText(find.byType(TextField).at(1), 'amangupta@gmail.com');
      await tester.enterText(find.byType(TextField).at(2), 'password123');

      await tester.tap(find.text('Sign up'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(find.text('Verify Code'), findsOneWidget);
      expect(AuthService.instance.isLoggedIn, isFalse);
      expect(find.text("Don't receive OTP?"), findsOneWidget);
      expect(find.text('Resend code'), findsOneWidget);
      expect(find.text('Verify'), findsOneWidget);

      // Tap Resend Code
      await tester.tap(find.text('Resend code'));
      await tester.pumpAndSettle();

      expect(find.text('A fresh 4-digit verification code has been sent!'), findsOneWidget);

      // Enter the 4-digit OTP
      final pinFields = find.byType(TextField);
      await tester.enterText(pinFields.at(0), '1');
      await tester.enterText(pinFields.at(1), '2');
      await tester.enterText(pinFields.at(2), '3');
      await tester.enterText(pinFields.at(3), '4');

      // Tap Verify
      await tester.tap(find.text('Verify'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(AuthService.instance.isLoggedIn, isTrue);
      expect(find.text('Quiz Category'), findsOneWidget);
    });

    testWidgets('ProfileScreen Log Out clears local session and redirects to SignInScreen', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await AuthService.instance.applyLocalSession(email: 'amangupta@gmail.com');
      expect(AuthService.instance.isLoggedIn, isTrue);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/profile'));
      await tester.pumpAndSettle();

      expect(find.text('Log Out'), findsOneWidget);

      // Tap Log Out button
      await tester.tap(find.text('Log Out'));
      await tester.pumpAndSettle();

      // Confirm dialog
      expect(find.text('Are you sure you want to log out of your account?'), findsOneWidget);
      await tester.tap(find.widgetWithText(ElevatedButton, 'Log Out').last);
      await tester.pumpAndSettle();

      expect(AuthService.instance.isLoggedIn, isFalse);
      expect(find.text('Welcome Back'), findsOneWidget);
    });

    testWidgets('SignInScreen Google Sign-In button triggers without crash', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/signin'));
      await tester.pumpAndSettle();

      expect(find.text('Continue with Google'), findsOneWidget);

      await tester.tap(find.text('Continue with Google'));
      await tester.pumpAndSettle();

      // In test harness without active Google OAuth popup, gracefully stays or handles auth
      expect(find.byType(QuizsApp), findsOneWidget);
    });

    testWidgets('SignUpScreen Google Sign-In button triggers without crash', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(const QuizsApp(initialRoute: '/signup'));
      await tester.pumpAndSettle();

      expect(find.text('Continue with Google'), findsOneWidget);

      await tester.tap(find.text('Continue with Google'));
      await tester.pumpAndSettle();

      expect(find.byType(QuizsApp), findsOneWidget);
    });

    testWidgets('EditProfileScreen renders and updates profile name', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await AuthService.instance.applyLocalSession(email: 'amangupta@gmail.com', name: 'Aman Gupta');

      await tester.pumpWidget(const QuizsApp(initialRoute: '/edit-profile'));
      await tester.pumpAndSettle();

      expect(find.text('Edit Profile'), findsOneWidget);
      expect(find.text('Name'), findsOneWidget);
      expect(find.text('Email'), findsOneWidget);
      expect(find.text('Change Password'), findsOneWidget);
      expect(find.text('Save Changes'), findsOneWidget);
      expect(find.text('Delete Account'), findsOneWidget);

      // Email field is read-only; only the name can be edited.
      await tester.enterText(find.byType(TextField).at(0), 'Rohit Sharma');

      await tester.tap(find.text('Save Changes'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(AuthService.instance.userName, 'Rohit Sharma');
      expect(AuthService.instance.userEmail, 'amangupta@gmail.com');
    });

    testWidgets('EditProfileScreen Delete Account deletes session and redirects to SignInScreen', (tester) async {
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await AuthService.instance.applyLocalSession(email: 'amangupta@gmail.com', name: 'Aman Gupta');

      await tester.pumpWidget(const QuizsApp(initialRoute: '/edit-profile'));
      await tester.pumpAndSettle();

      // Tap Delete Account
      await tester.tap(find.text('Delete Account'));
      await tester.pumpAndSettle();

      // Dialog appears
      expect(find.text('Delete Account'), findsWidgets);
      expect(find.text('Are you sure you want to permanently delete your account? This action cannot be undone.'), findsOneWidget);

      // Tap confirm Delete
      await tester.tap(find.widgetWithText(ElevatedButton, 'Delete'));
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();

      expect(AuthService.instance.isLoggedIn, isFalse);
      expect(find.text('Welcome Back'), findsOneWidget);
    });
  });
}
