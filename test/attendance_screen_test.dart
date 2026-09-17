import 'dart:convert';

import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:quizs/quizs_app.dart';
import 'package:quizs/services/api_client.dart';
import 'package:quizs/services/auth_service.dart';

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

  final mockClient = MockClient((request) async {
    if (request.url.path.endsWith('/attendance/today')) {
      return http.Response(
        jsonEncode({
          'success': true,
          'attendance': {
            'date': '2026-09-17',
            'status': 'present',
            'note': 'On-time arrival',
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }
    if (request.url.path.endsWith('/attendance/summary')) {
      return http.Response(
        jsonEncode({
          'success': true,
          'summary': {
            'present_days': 18,
            'absent_days': 2,
            'leave_days': 1,
            'attendance_percentage': 85.7,
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }
    if (request.url.path.endsWith('/attendance/history')) {
      return http.Response(
        jsonEncode({
          'success': true,
          'history': {
            'data': [
              {
                'date': '2026-09-17',
                'status': 'present',
                'note': 'Regular class session',
              },
              {
                'date': '2026-09-16',
                'status': 'present',
                'note': 'Quiz day',
              },
              {
                'date': '2026-09-15',
                'status': 'absent',
                'note': 'Unexcused',
              },
            ],
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }
    return http.Response(jsonEncode({'success': true}), 200);
  });

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    ApiClient.instance.debugClient = mockClient;
    await AuthService.instance.applyLocalSession(email: 'test@example.com');
  });

  testWidgets('Attendance screen renders elevated UI, cards, and typography', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(() => tester.view.resetPhysicalSize());

    await tester.pumpWidget(const QuizsApp(initialRoute: '/attendance'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));
    await tester.pumpAndSettle();

    // Verify header and subtitle
    expect(find.text('Attendance'), findsWidgets);
    expect(find.text('Official School Attendance Log'), findsOneWidget);

    // Verify today's status card
    expect(find.text("TODAY'S STATUS"), findsOneWidget);
    expect(find.text('Present'), findsWidgets);
    expect(find.text('2026-09-17'), findsWidgets);

    // Verify summary tiles
    expect(find.text('Present'), findsWidgets);
    expect(find.text('Absent'), findsWidgets);
    expect(find.text('Leave'), findsWidgets);
    expect(find.text('18'), findsOneWidget);
    expect(find.text('2'), findsOneWidget);
    expect(find.text('1'), findsOneWidget);

    // Verify overall rate card
    expect(find.text('Overall Rate'), findsOneWidget);
    expect(find.text('Great'), findsOneWidget);
    expect(find.text('86%'), findsOneWidget);
    expect(find.text('18 of 21 sessions attended'), findsOneWidget);

    // Verify attendance log header and items
    expect(find.text('Attendance Log'), findsOneWidget);
    expect(find.text('3'), findsOneWidget);
    expect(find.text('Regular class session'), findsOneWidget);
    expect(find.text('Quiz day'), findsOneWidget);
    expect(find.text('Unexcused'), findsOneWidget);
  });

  testWidgets('Attendance screen renders empty state cleanly without overflow', (tester) async {
    final emptyMockClient = MockClient((request) async {
      if (request.url.path.endsWith('/attendance/today')) {
        return http.Response(jsonEncode({'success': true, 'attendance': null}), 200);
      }
      if (request.url.path.endsWith('/attendance/summary')) {
        return http.Response(jsonEncode({'success': true, 'summary': {}}), 200);
      }
      if (request.url.path.endsWith('/attendance/history')) {
        return http.Response(jsonEncode({'success': true, 'history': {'data': []}}), 200);
      }
      return http.Response(jsonEncode({'success': true}), 200);
    });

    ApiClient.instance.debugClient = emptyMockClient;

    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(() => tester.view.resetPhysicalSize());

    await tester.pumpWidget(const QuizsApp(initialRoute: '/attendance'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));
    await tester.pumpAndSettle();

    expect(find.text('No Attendance Recorded Yet'), findsOneWidget);
    expect(find.text('Your attendance records will appear here as your teachers mark it.'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
