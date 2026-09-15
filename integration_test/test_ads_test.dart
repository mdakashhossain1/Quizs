import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';
import 'package:integration_test/integration_test.dart';
import 'package:quizs/ads/test_ads.dart';
import 'package:quizs/quizs_app.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Native test banners load on all seven screens', (tester) async {
    TestAds.start();
    for (final route in [
      '/',
      '/categories',
      '/mathematics',
      '/achievements',
      '/question',
      '/explanation',
      '/results',
    ]) {
      await tester.pumpWidget(const SizedBox.shrink());
      await tester.pumpWidget(QuizsApp(initialRoute: route));
      final deadline = DateTime.now().add(const Duration(seconds: 60));
      while (find.byType(AdWidget).evaluate().isEmpty &&
          DateTime.now().isBefore(deadline)) {
        await tester.pump(const Duration(seconds: 1));
      }
      expect(find.byType(AdWidget), findsOneWidget, reason: 'Banner on $route');
      await tester.ensureVisible(find.byType(AdWidget));
      await tester.pump(const Duration(seconds: 3));
      expect(tester.takeException(), isNull);
      debugPrint('Verified native test banner on $route');
    }
  }, timeout: const Timeout(Duration(minutes: 10)));
}
