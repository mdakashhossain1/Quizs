import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:quizs/l10n/app_strings.dart';
import 'package:quizs/quizs_app.dart';
import 'package:quizs/services/api_client.dart';
import 'package:quizs/services/auth_service.dart';
import 'package:quizs/services/quiz_api_service.dart';
import 'package:quizs/widgets/quiz_bottom_nav.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    TestWidgetsFlutterBinding.ensureInitialized()
        .platformDispatcher
        .accessibilityFeaturesTestValue = const FakeAccessibilityFeatures(
      disableAnimations: true,
    );
    QuizApiService.instance.invalidateCache();
    ApiClient.instance.debugClient = MockClient((request) async {
      if (request.url.path.endsWith('/categories')) {
        return http.Response(
          jsonEncode({
            'success': true,
            'categories': [
              {'id': 1, 'name': 'Science', 'slug': 'science', 'color': '#4F46E5', 'quizzes_count': 3},
              {'id': 2, 'name': 'Maths', 'slug': 'maths', 'color': '#10B981', 'quizzes_count': 2},
              {'id': 3, 'name': 'GK', 'slug': 'gk', 'color': '#F59E0B', 'quizzes_count': 4},
            ],
          }),
          200,
          headers: {'content-type': 'application/json'},
        );
      }
      return http.Response('{}', 200);
    });
    AppLanguage.instance.setLanguage(AppLanguageType.english);
    await AuthService.instance.applyLocalSession(email: 'test@example.com');
  });

  tearDown(() {
    TestWidgetsFlutterBinding.ensureInitialized()
        .platformDispatcher
        .clearAccessibilityFeaturesTestValue();
    AppLanguage.instance.setLanguage(AppLanguageType.english);
    ApiClient.instance.debugClient = http.Client();
    QuizApiService.instance.invalidateCache();
  });

  testWidgets('App language defaults to English and switches to Hindi reactively', (tester) async {
    tester.view.physicalSize = const Size(412, 917);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(const QuizsApp(initialRoute: '/profile'));
    await tester.pumpAndSettle();

    // Verify English text on Profile
    expect(find.text('Edit Profile'), findsOneWidget);
    expect(find.text('Language (English)'), findsOneWidget);
    expect(find.text('Terms & Conditions'), findsOneWidget);
    expect(find.text('Privacy Policy'), findsOneWidget);
    expect(find.text('Log Out'), findsOneWidget);

    // Tap language row to open modal
    await tester.tap(find.text('Language (English)'));
    await tester.pumpAndSettle();

    // Verify only English and Hindi are present
    expect(find.text('Select Language'), findsOneWidget);
    expect(find.text('English'), findsWidgets);
    expect(find.text('हिंदी (Hindi)'), findsOneWidget);
    expect(find.text('Español (Spanish)'), findsNothing);
    expect(find.text('Français (French)'), findsNothing);

    // Select Hindi
    await tester.tap(find.text('हिंदी (Hindi)'));
    await tester.pumpAndSettle();

    // Verify entire profile screen is in Hindi
    expect(find.text('प्रोफ़ाइल संपादित करें'), findsOneWidget);
    expect(find.text('भाषा (हिंदी)'), findsOneWidget);
    expect(find.text('नियम एवं शर्तें'), findsOneWidget);
    expect(find.text('गोपनीयता नीति'), findsOneWidget);
    expect(find.text('लॉग आउट'), findsOneWidget);

    // Switch to Category via bottom navigation
    await tester.tap(find.descendant(of: find.byType(QuizBottomNav), matching: find.bySemanticsLabel('Category')));
    await tester.pumpAndSettle();

    expect(find.text('श्रेणी'), findsWidgets);
    expect(find.text('विज्ञान'), findsWidgets);
    expect(find.text('गणित'), findsWidgets);


    // Switch to Home via bottom navigation
    await tester.tap(find.descendant(of: find.byType(QuizBottomNav), matching: find.bySemanticsLabel('Home')));
    await tester.pumpAndSettle();

    expect(find.text('स्वागत है'), findsOneWidget);
    expect(find.text('क्विज़ श्रेणी'), findsOneWidget);
    expect(find.text('विज्ञान'), findsOneWidget);
    expect(find.text('गणित'), findsOneWidget);

    // Switch back to English
    AppLanguage.instance.setLanguage(AppLanguageType.english);
    await tester.pumpAndSettle();

    expect(find.text('Welcome'), findsOneWidget);
    expect(find.text('Quiz Category'), findsOneWidget);
    expect(find.text('Science'), findsOneWidget);
    expect(find.text('Maths'), findsOneWidget);
  });
}
