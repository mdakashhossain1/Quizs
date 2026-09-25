import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:quizs/l10n/app_strings.dart';
import 'package:quizs/screens/home_screen.dart';
import 'package:quizs/screens/offerwall_screen.dart';
import 'package:quizs/services/api_client.dart';
import 'package:quizs/services/offerwall_service.dart';
import 'package:quizs/widgets/quiz_bottom_nav.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  const url = 'https://rewards.unity.com/owp/web/link/test-key/u/42';

  setUp(() {
    ApiClient.instance.setToken('test-token');
    AppLanguage.instance.setLanguage(AppLanguageType.english);
  });

  tearDown(() {
    ApiClient.instance.setToken(null);
    ApiClient.instance.debugClient = http.Client();
    AppLanguage.instance.setLanguage(AppLanguageType.english);
  });

  test(
    'uses authenticated API and launches the URL without a completion call',
    () async {
      final requests = <http.Request>[];
      Uri? opened;
      ApiClient.instance.debugClient = MockClient((request) async {
        requests.add(request);
        return http.Response(jsonEncode({'url': url}), 200);
      });
      await OfferwallService(
        launcher: (uri) async {
          opened = uri;
          return true;
        },
      ).open();
      expect(opened.toString(), url);
      expect(requests, hasLength(1));
      expect(requests.single.method, 'POST');
      expect(requests.single.url.path, '/api/offerwall/launch');
      expect(requests.single.headers['Authorization'], 'Bearer test-token');
      expect(jsonDecode(requests.single.body), isEmpty);
    },
  );

  test('rejects missing URLs and untrusted destinations', () async {
    for (final destination in [
      null,
      'https://example.com/owp/web/link/key/u/42',
      'http://rewards.unity.com/owp/web/link/key/u/42',
      'https://rewards.unity.com:444/owp/web/link/key/u/42',
      'https://user@rewards.unity.com/owp/web/link/key/u/42',
    ]) {
      ApiClient.instance.debugClient = MockClient(
        (_) async => http.Response(jsonEncode({'url': destination}), 200),
      );
      await expectLater(
        OfferwallService(
          launcher: (_) async {
            fail('Invalid URLs must not be opened.');
          },
        ).open(),
        throwsA(isA<ApiException>()),
      );
    }
  });

  test('network and platform launch failures propagate', () async {
    ApiClient.instance.debugClient = MockClient((_) async {
      throw http.ClientException('offline');
    });
    final service = OfferwallService(launcher: (_) async => false);
    await expectLater(service.open(), throwsA(isA<ApiException>()));
    ApiClient.instance.debugClient = MockClient(
      (_) async => http.Response(jsonEncode({'url': url}), 200),
    );
    await expectLater(service.open(), throwsA(isA<ApiException>()));
  });

  testWidgets('shows loading, retries failures, and never claims completion', (
    tester,
  ) async {
    final pending = Completer<http.Response>();
    var attempts = 0;
    ApiClient.instance.debugClient = MockClient((_) {
      attempts++;
      return attempts == 1
          ? pending.future
          : Future.value(http.Response(jsonEncode({'url': url}), 200));
    });
    await tester.pumpWidget(
      MaterialApp(
        home: OfferwallScreen(
          service: OfferwallService(launcher: (_) async => true),
        ),
      ),
    );
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
    expect(find.byType(FilledButton), findsNothing);
    pending.complete(http.Response('{}', 503));
    await tester.pumpAndSettle();
    expect(find.text(AppStrings.t('offerwall_unavailable')), findsOneWidget);
    await tester.tap(find.text('Retry'));
    await tester.pumpAndSettle();
    expect(attempts, 2);
    expect(find.text('Open Offerwall'), findsOneWidget);
    expect(find.textContaining('completed'), findsNothing);
  });

  testWidgets('expired session offers a sign-in route', (tester) async {
    ApiClient.instance.debugClient = MockClient(
      (_) async => http.Response('{}', 401),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: const OfferwallScreen(),
        routes: {
          '/signin': (_) => const Scaffold(body: Text('Sign-in destination')),
        },
      ),
    );
    await tester.pumpAndSettle();
    expect(
      find.text(AppStrings.t('offerwall_signin_required')),
      findsOneWidget,
    );
    await tester.tap(find.text('Continue'));
    await tester.pumpAndSettle();
    expect(find.text('Sign-in destination'), findsOneWidget);
  });

  testWidgets('temporary password routes to password change', (tester) async {
    ApiClient.instance.debugClient = MockClient(
      (_) async =>
          http.Response(jsonEncode({'must_change_password': true}), 403),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: const OfferwallScreen(),
        routes: {
          '/force-change-password': (_) =>
              const Scaffold(body: Text('Password destination')),
        },
      ),
    );
    await tester.pumpAndSettle();
    await tester.tap(find.text('Continue'));
    await tester.pumpAndSettle();
    expect(find.text('Password destination'), findsOneWidget);
  });

  testWidgets('leaving while loading does not update a disposed screen', (
    tester,
  ) async {
    final pending = Completer<http.Response>();
    ApiClient.instance.debugClient = MockClient((_) => pending.future);
    await tester.pumpWidget(
      MaterialApp(
        home: OfferwallScreen(
          service: OfferwallService(
            launcher: (_) async {
              fail('A closed screen must not launch the browser.');
            },
          ),
        ),
      ),
    );
    await tester.pumpWidget(const SizedBox());
    pending.complete(http.Response(jsonEncode({'url': url}), 200));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
  });

  for (final language in AppLanguageType.values) {
    testWidgets('bottom tab opens Offerwall without scrolling in $language', (
      tester,
    ) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      AppLanguage.instance.setLanguage(language);
      ApiClient.instance.debugClient = MockClient(
        (_) async => http.Response('{}', 200),
      );
      await tester.pumpWidget(
        MaterialApp(
          home: const HomeScreen(),
          routes: {
            '/offerwall': (_) =>
                const Scaffold(body: Text('Offerwall destination')),
          },
        ),
      );
      await tester.pumpAndSettle();
      final tab = find.descendant(
        of: find.byType(QuizBottomNav),
        matching: find.byTooltip(AppStrings.t('offerwall')),
      );
      expect(tab, findsOneWidget);
      expect(find.text('Explore available offers'), findsNothing);
      await tester.tap(tab);
      await tester.pumpAndSettle();
      expect(find.text('Offerwall destination'), findsOneWidget);
      expect(tester.takeException(), isNull);
    });
  }
}
