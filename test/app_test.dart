import 'dart:convert';
import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:quizs/quizs_app.dart';
import 'package:quizs/ads/banner_ad_slot.dart';
import 'package:quizs/services/api_client.dart';
import 'package:quizs/services/auth_service.dart';
import 'package:quizs/widgets/design_widgets.dart';
import 'package:quizs/widgets/quiz_bottom_nav.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Fixture categories mirroring the real backend's seeded ones, keyed by the
/// slugs `QuizApiService` looks up.
const _mockCategories = [
  {'id': 1, 'name': 'Science & Nature', 'slug': 'science-nature'},
  {'id': 2, 'name': 'Mathematics & Logic', 'slug': 'mathematics-logic'},
  {'id': 3, 'name': 'General Knowledge', 'slug': 'general-knowledge'},
];

Map<String, dynamic> _mockQuizDetail(int quizId) => {
      'success': true,
      'quiz': {
        'id': quizId,
        'title': 'Sample Quiz $quizId',
        'questions': List.generate(
          2,
          (i) => {
            'id': quizId * 10 + i,
            'question_text': 'Sample question ${i + 1}?',
            'meaning': 'Meaning ${i + 1}',
            'explanation': 'Explanation ${i + 1}',
            'options': [
              {'id': quizId * 100 + i * 4 + 1, 'option_text': 'Option A', 'is_correct': true},
              {'id': quizId * 100 + i * 4 + 2, 'option_text': 'Option B', 'is_correct': false},
              {'id': quizId * 100 + i * 4 + 3, 'option_text': 'Option C', 'is_correct': false},
              {'id': quizId * 100 + i * 4 + 4, 'option_text': 'Option D', 'is_correct': false},
            ],
          },
        ),
      },
    };

final _quizzesByCategoryPattern = RegExp(r'/categories/(\d+)/quizzes$');
final _quizDetailPattern = RegExp(r'/quizzes/(\d+)$');

/// Stubs the leaderboard + quiz-content endpoints so screens that fetch them
/// (ResultsScreen, SelectionScreen, QuestionScreen) render deterministically
/// in tests without needing a live backend.
final _mockApiClient = MockClient((request) async {
  if (request.url.path.endsWith('/leaderboard')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'leaderboard': List.generate(
          5,
          (i) => {'name': 'Test User ${i + 1}', 'score': 100 - i * 10, 'streak': 5 - i},
        ),
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/categories')) {
    return http.Response(
      jsonEncode({'success': true, 'categories': _mockCategories}),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  final categoryMatch = _quizzesByCategoryPattern.firstMatch(request.url.path);
  if (categoryMatch != null) {
    final categoryId = int.parse(categoryMatch.group(1)!);
    return http.Response(
      jsonEncode({
        'success': true,
        'category': _mockCategories.firstWhere((c) => c['id'] == categoryId),
        'quizzes': List.generate(
          2,
          (i) => {
            'id': categoryId * 100 + i + 1,
            'title': 'Sample Quiz ${i + 1}',
            'questions_count': 2,
          },
        ),
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  final detailMatch = _quizDetailPattern.firstMatch(request.url.path);
  if (detailMatch != null) {
    final quizId = int.parse(detailMatch.group(1)!);
    return http.Response(
      jsonEncode(_mockQuizDetail(quizId)),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/profile/stats')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'stats': {
          'level': {'level': 20, 'xp': 1950, 'xp_into_level': 50, 'xp_for_next_level': 100, 'completed_target_days': 195},
          'today_target': {'effective_target': 20, 'completed_quizzes': 5, 'remaining': 15, 'progress_percentage': 25, 'status': 'in_progress'},
          'accuracy': 87,
          'quiz_played': 132,
          'right': 8,
          'wrong': 2,
          'this_month': 35,
          'rank': 70,
        },
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/achievement')) {
    return http.Response(
      jsonEncode({
        'success': true,
        'achievement': {
          'profile': {
            'name': 'Test User',
            'avatar': null,
            'level': {'level': 20, 'xp': 1950, 'xp_into_level': 50, 'xp_for_next_level': 100},
            'accuracy': 87,
            'today_target': {'effective_target': 20, 'completed_quizzes': 5, 'remaining': 15, 'progress_percentage': 25, 'status': 'in_progress'},
          },
          'active_users': {'active_users': 42, 'active_this_month': 18},
          'ranking': {
            'top': [
              {'user_id': 1, 'name': 'Test User', 'avatar': null, 'level': 20, 'xp': 1950, 'accuracy': 87, 'quiz_played': 132, 'score': 900},
              {'user_id': 2, 'name': 'Other User', 'avatar': null, 'level': 15, 'xp': 1400, 'accuracy': 80, 'quiz_played': 90, 'score': 700},
            ],
            'your_rank': 1,
            'total_eligible_users': 70,
          },
        },
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  if (request.url.path.endsWith('/notifications')) {
    return http.Response(
      jsonEncode({'success': true, 'notifications': []}),
      200,
      headers: {'content-type': 'application/json'},
    );
  }

  return http.Response(jsonEncode({'success': false}), 404);
});


Future<void> loadFonts() async {
  final fonts = <String, List<String>>{
    'Poppins': [
      'Poppins-Light.ttf',
      'Poppins-Regular.ttf',
      'Poppins-Medium.ttf',
      'Poppins-SemiBold.ttf',
      'Poppins-Bold.ttf',
      'Poppins-ExtraBold.ttf',
    ],
    'Quizlo': ['Quizlo-DEMO.otf'],
    'HappySchool': ['Happy-School.ttf'],
    'DaysOne': ['DaysOne-Regular.ttf'],
    'Quicksand': ['Quicksand.ttf'],
    'Questrial': ['Questrial-Regular.ttf'],
  };
  for (final entry in fonts.entries) {
    final loader = FontLoader(entry.key);
    for (final file in entry.value) {
      loader.addFont(rootBundle.load('assets/fonts/$file'));
    }
    await loader.load();
  }
}

Future<void> settleImages(WidgetTester tester) async {
  await tester.runAsync(() async {
    final context = tester.element(find.byType(DesignCanvas).last);
    for (final file in Directory('assets/figma').listSync().whereType<File>()) {
      if (file.path.endsWith('.png')) {
        await precacheImage(
          AssetImage(file.path.replaceAll('\\', '/')),
          context,
        );
      }
    }
  });
  await tester.pumpAndSettle();
}

Future<void> tapAction(WidgetTester tester, String name) async {
  final finder = find
      .byWidgetPredicate(
        (widget) => widget is DesignAction && widget.label == name,
      )
      .first;
  await tester.ensureVisible(finder);
  await tester.tap(finder);
  await tester.pumpAndSettle();
}

void main() {
  setUpAll(() async {
    await loadFonts();
  });

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    TestWidgetsFlutterBinding.ensureInitialized()
        .platformDispatcher
        .accessibilityFeaturesTestValue = const FakeAccessibilityFeatures(
      disableAnimations: true,
    );
    ApiClient.instance.debugClient = _mockApiClient;
    await AuthService.instance.applyLocalSession(email: 'test@example.com');
  });
  tearDown(() {
    TestWidgetsFlutterBinding.ensureInitialized()
        .platformDispatcher
        .clearAccessibilityFeaturesTestValue();
  });

  testWidgets('Static navigation reaches the supplied screens', (tester) async {
    tester.view.physicalSize = const Size(412, 917);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(const QuizsApp());
    await settleImages(tester);
    expect(find.text('Welcome'), findsOneWidget);
    await tapAction(tester, 'Browse categories');
    expect(find.text('Choose category'), findsOneWidget);
    await tester.tap(find.text('Mathematics & Logic'));
    await tester.pumpAndSettle();
    expect(find.text('Sample Quiz 1'), findsOneWidget);
    await tester.tap(find.text('Sample Quiz 1'));
    await tester.pumpAndSettle();
    expect(find.text('Sample question 1?'), findsOneWidget);
    await tapAction(tester, 'View answer explanation for Option A');
    await tapAction(tester, 'Next question');
    expect(find.text('Sample question 2?'), findsOneWidget);
    await tapAction(tester, 'View answer explanation for Option A');
    await tapAction(tester, 'Next question');
    expect(find.text('Congratulations !'), findsOneWidget);
    await tapAction(tester, 'Return home');
    expect(find.text('Welcome'), findsOneWidget);

    await tapAction(tester, 'Achievements');
    await tester.pumpAndSettle();
    expect(find.text('Global Ranking'), findsOneWidget);
    expect(find.text('Other User'), findsOneWidget);
    await tester.tap(find.byIcon(Icons.arrow_back_ios_new_rounded));
    await tester.pumpAndSettle();
    expect(find.text('Welcome'), findsOneWidget);
    await tapAction(tester, 'Notifications');
    expect(find.text('No notifications yet.'), findsOneWidget);
    await tapAction(tester, 'Back');
    expect(find.text('Welcome'), findsOneWidget);
    await tapAction(tester, 'Category');
    expect(find.text('Choose category'), findsOneWidget);
    await tapAction(tester, 'Back');
    expect(find.text('Welcome'), findsOneWidget);
    await tapAction(tester, 'Profile');

    expect(find.text('Edit Profile'), findsOneWidget);
    expect(find.text('Privacy Policy'), findsOneWidget);
    expect(find.text('Log Out'), findsOneWidget);
    await tapAction(tester, 'Back');
    expect(find.text('Welcome'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  const routes = {
    'home': '/',
    'dashboard': '/dashboard',
    'profile': '/profile',
    'notifications': '/notifications',
    'categories': '/categories',
    'mathematics': '/mathematics',
    'question': '/question',
    'explanation': '/explanation',
    'results': '/results',
  };

  for (final entry in routes.entries) {
    testWidgets('${entry.key} renders at Figma size', (tester) async {
      debugDisableShadows = false;
      tester.view.physicalSize = const Size(412, 917);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      await tester.pumpWidget(QuizsApp(initialRoute: entry.value));
      await settleImages(tester);
      expect(tester.takeException(), isNull);
      final banner = find.byType(BannerAdSlot);
      expect(banner, findsOneWidget);
      expect(tester.getSize(banner).height, BannerAdSlot.height);
      expect(find.text('ADVERTISEMENT'), findsOneWidget);
      expect(
        find.ancestor(of: banner, matching: find.byType(FittedBox)),
        findsNothing,
      );
      final boundary = tester.renderObject<RenderRepaintBoundary>(
        find.byKey(const ValueKey('design-canvas')).last,
      );
      await tester.runAsync(() async {
        final image = await boundary.toImage();
        final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
        final file = File('design/implementation/${entry.key}.png');
        await file.parent.create(recursive: true);
        await file.writeAsBytes(bytes!.buffer.asUint8List());
        image.dispose();
      });
      debugDisableShadows = true;
    });
  }

  testWidgets('Narrow phones can scroll to navigation without overflow', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(const QuizsApp());
    await settleImages(tester);
    final banner = find.byType(BannerAdSlot);
    expect(tester.getSize(banner).height, BannerAdSlot.height);
    final canvas = tester.widget<DesignCanvas>(find.byType(DesignCanvas));
    expect(
      tester.getBottomRight(banner).dy + 12,
      lessThanOrEqualTo(canvas.adBefore! * 360 / 412 + 0.01),
    );
    await tapAction(tester, 'Browse categories');
    expect(find.text('Choose category'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('Bottom navigation remains sticky during content scrolling', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(const QuizsApp());
    await settleImages(tester);

    final navFinder = find.byType(QuizBottomNav);
    expect(navFinder, findsOneWidget);
    final initialNavPos = tester.getTopLeft(navFinder);

    // Scroll the content down
    await tester.drag(find.byType(SingleChildScrollView), const Offset(0, -150));
    await tester.pumpAndSettle();

    final scrolledNavPos = tester.getTopLeft(navFinder);
    // Sticky bottom nav position should not move when content scrolls
    expect(scrolledNavPos.dy, equals(initialNavPos.dy));

    // Navigating to Category via sticky nav
    await tapAction(tester, 'Category');
    expect(find.text('Choose category'), findsOneWidget);

    final categoryNavFinder = find.byType(QuizBottomNav);
    expect(categoryNavFinder, findsOneWidget);
    final categoryNavPos = tester.getTopLeft(categoryNavFinder);
    expect(categoryNavPos.dy, equals(initialNavPos.dy));

    // Scroll category content
    await tester.drag(find.byType(SingleChildScrollView), const Offset(0, -150));
    await tester.pumpAndSettle();
    expect(tester.getTopLeft(categoryNavFinder).dy, equals(initialNavPos.dy));
    expect(tester.takeException(), isNull);
  });

  testWidgets('Top back button on Category and Profile restores Home active tab', (tester) async {
    tester.view.physicalSize = const Size(412, 917);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(const QuizsApp());
    await settleImages(tester);

    // Navigate to Category
    await tapAction(tester, 'Category');
    expect(find.text('Choose category'), findsOneWidget);
    var nav = tester.widget<QuizBottomNav>(find.byType(QuizBottomNav));
    expect(nav.initialIndex, equals(1));

    // Tap top back arrow
    await tapAction(tester, 'Back');
    expect(find.text('Welcome'), findsOneWidget);
    nav = tester.widget<QuizBottomNav>(find.byType(QuizBottomNav));
    expect(nav.initialIndex, equals(0));

    // Navigate to Profile
    await tapAction(tester, 'Profile');
    expect(find.text('Edit Profile'), findsOneWidget);
    nav = tester.widget<QuizBottomNav>(find.byType(QuizBottomNav));
    expect(nav.initialIndex, equals(3));

    // Tap top back arrow
    await tapAction(tester, 'Back');
    expect(find.text('Welcome'), findsOneWidget);
    nav = tester.widget<QuizBottomNav>(find.byType(QuizBottomNav));
    expect(nav.initialIndex, equals(0));
  });
}

