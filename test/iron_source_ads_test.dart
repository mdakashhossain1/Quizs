import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:quizs/services/iron_source_service.dart';
import 'package:quizs/widgets/iron_source_banner_slot.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('IronSourceService Tests', () {
    final service = IronSourceService.instance;

    test('Configuration constants are correct', () {
      expect(IronSourceService.appKey, equals('2840c05d5'));
      expect(IronSourceService.interstitialAdUnitId, equals('kfr3nr8bza92d3sh'));
      expect(IronSourceService.bannerAdUnitId, equals('k7qwv1lomq8xdnji'));
    });

    test('Default service state before native initialization', () {
      // In unit test environment (desktop host), native SDK is not initialized
      expect(service.isInitialized, isFalse);
      expect(service.isAdLoaded, isFalse);
      expect(service.isLoadingAd, isFalse);
    });

    test('showInterstitialAd executes onComplete callback safely when ad not ready', () async {
      bool completed = false;
      await service.showInterstitialAd(
        onComplete: () {
          completed = true;
        },
      );
      expect(completed, isTrue);
    });
  });

  group('IronSourceBannerSlot Widget Tests', () {
    testWidgets('Renders 320x50 banner slot with advertisement label', (
      WidgetTester tester,
    ) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            body: Center(
              child: IronSourceBannerSlot(),
            ),
          ),
        ),
      );

      await tester.pump();

      // Verify dimensions
      expect(IronSourceBannerSlot.width, equals(320));
      expect(IronSourceBannerSlot.adHeight, equals(50));
      expect(IronSourceBannerSlot.labelHeight, equals(16));
      expect(IronSourceBannerSlot.height, equals(66));

      // Verify advertisement label is present
      expect(find.text('ADVERTISEMENT'), findsOneWidget);

      // Verify fallback container displays in test environment
      expect(find.text('IronSource Ads (Mobile only)'), findsOneWidget);
    });

    testWidgets('Renders properly under Dark Mode theme', (
      WidgetTester tester,
    ) async {
      await tester.pumpWidget(
        MaterialApp(
          theme: ThemeData.dark(),
          home: const Scaffold(
            body: Center(
              child: IronSourceBannerSlot(isDark: true),
            ),
          ),
        ),
      );

      await tester.pump();

      expect(find.text('ADVERTISEMENT'), findsOneWidget);
      expect(find.byType(IronSourceBannerSlot), findsOneWidget);
    });

    testWidgets('Renders properly in constrained narrow screen width', (
      WidgetTester tester,
    ) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            body: Center(
              child: SizedBox(
                width: 280, // narrower than 320
                child: IronSourceBannerSlot(),
              ),
            ),
          ),
        ),
      );

      await tester.pump();

      expect(find.text('ADVERTISEMENT'), findsOneWidget);
    });
  });
}
