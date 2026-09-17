import 'dart:convert';

import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:quizs/quizs_app.dart';
import 'package:quizs/services/api_client.dart';
import 'package:quizs/services/auth_service.dart';

/// push_notification_prd.md: the in-app inbox must render real
/// server-driven notifications (never fabricated demo entries) and mark
/// them read against the backend on tap / "mark all read".
Future<void> _loadFonts() async {
  final loader = FontLoader('Poppins');
  for (final file in [
    'Poppins-Light.ttf',
    'Poppins-Regular.ttf',
    'Poppins-Medium.ttf',
    'Poppins-SemiBold.ttf',
    'Poppins-Bold.ttf',
    'Poppins-ExtraBold.ttf',
  ]) {
    loader.addFont(rootBundle.load('assets/fonts/$file'));
  }
  await loader.load();
}

void main() {
  setUpAll(_loadFonts);

  int readCalls = 0;
  int readAllCalls = 0;
  bool failReadAll = false;
  bool failRead = false;

  final mockClient = MockClient((request) async {
    if (request.url.path.endsWith('/notifications') && request.method == 'GET') {
      return http.Response(
        jsonEncode({
          'success': true,
          'notifications': [
            {
              'id': 1,
              'title': 'Attendance Updated',
              'body': 'Your attendance for today has been marked as Present.',
              'image_url': null,
              'destination_type': 'none',
              'destination_id': null,
              'created_at': DateTime.now().subtract(const Duration(minutes: 5)).toIso8601String(),
              'is_read': false,
            },
            {
              'id': 2,
              'title': 'Level Up!',
              'body': 'You reached a new level.',
              'image_url': null,
              'destination_type': 'achievement',
              'destination_id': null,
              'created_at': DateTime.now().subtract(const Duration(days: 1)).toIso8601String(),
              'is_read': true,
            },
          ],
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    if (request.url.path.endsWith('/notifications/read-all')) {
      readAllCalls++;
      if (failReadAll) {
        return http.Response(jsonEncode({'success': false}), 500, headers: {'content-type': 'application/json'});
      }
      return http.Response(jsonEncode({'success': true}), 200, headers: {'content-type': 'application/json'});
    }

    if (request.url.path.contains('/notifications/') && request.url.path.endsWith('/read')) {
      readCalls++;
      if (failRead) {
        return http.Response(jsonEncode({'success': false}), 500, headers: {'content-type': 'application/json'});
      }
      return http.Response(jsonEncode({'success': true}), 200, headers: {'content-type': 'application/json'});
    }

    return http.Response(jsonEncode({'success': false}), 404);
  });

  setUp(() async {
    readCalls = 0;
    readAllCalls = 0;
    failReadAll = false;
    failRead = false;
    SharedPreferences.setMockInitialValues({});
    ApiClient.instance.debugClient = mockClient;
    await AuthService.instance.applyLocalSession(email: 'test@example.com');
  });

  void setPhoneSize(WidgetTester tester) {
    tester.view.physicalSize = const Size(412, 917);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
  }

  testWidgets('renders real backend notifications, not fabricated demo entries', (tester) async {
    setPhoneSize(tester);
    await tester.pumpWidget(const QuizsApp(initialRoute: '/notifications'));
    await tester.pumpAndSettle();

    expect(find.text('Attendance Updated'), findsOneWidget);
    expect(find.text('Level Up!'), findsOneWidget);
    expect(find.text('Daily Sprint Challenge is Live!'), findsNothing);
  });

  testWidgets('tapping an unread notification marks it read on the backend', (tester) async {
    setPhoneSize(tester);
    await tester.pumpWidget(const QuizsApp(initialRoute: '/notifications'));
    await tester.pumpAndSettle();

    await tester.tap(find.text('Attendance Updated'));
    await tester.pumpAndSettle();

    expect(readCalls, 1);
  });

  testWidgets('mark all read calls the backend and clears unread state', (tester) async {
    setPhoneSize(tester);
    await tester.pumpWidget(const QuizsApp(initialRoute: '/notifications'));
    await tester.pumpAndSettle();

    await tester.tap(find.text('Mark all read'));
    await tester.pumpAndSettle();

    expect(readAllCalls, 1);
  });

  testWidgets('a failed mark-all-read rolls back the optimistic UI update instead of claiming success', (tester) async {
    failReadAll = true;
    setPhoneSize(tester);
    await tester.pumpWidget(const QuizsApp(initialRoute: '/notifications'));
    await tester.pumpAndSettle();

    await tester.tap(find.text('Mark all read'));
    await tester.pumpAndSettle();

    expect(find.text('All notifications marked as read'), findsNothing);
    expect(find.text('Could not mark notifications as read. Please try again.'), findsOneWidget);

    // The rollback must actually restore the unread state server-side too:
    // tapping the same notification again should still attempt to mark it
    // read, since the earlier attempt never succeeded.
    await tester.tap(find.text('Attendance Updated'));
    await tester.pumpAndSettle();
    expect(readCalls, 1);
  });

  testWidgets('a failed mark-read on tap rolls back the optimistic UI update', (tester) async {
    failRead = true;
    setPhoneSize(tester);
    await tester.pumpWidget(const QuizsApp(initialRoute: '/notifications'));
    await tester.pumpAndSettle();

    await tester.tap(find.text('Attendance Updated'));
    await tester.pumpAndSettle();
    expect(readCalls, 1);

    // Rolled back to unread, so a second tap must retry the API call rather
    // than silently no-op'ing on a client-side state that was never real.
    failRead = false;
    await tester.tap(find.text('Attendance Updated'));
    await tester.pumpAndSettle();
    expect(readCalls, 2);
  });
}
