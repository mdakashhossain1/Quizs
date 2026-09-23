import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:quizs/screens/profile_screen.dart';
import 'package:quizs/services/app_review_service.dart';
import 'package:quizs/services/app_update_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('AppUpdateService Tests', () {
    final updateService = AppUpdateService.instance;

    test('isSupported is safely false in Flutter test environment', () {
      // Platform.environment['FLUTTER_TEST'] prevents native Play Core call during testing
      expect(updateService.isSupported, isFalse);
    });

    test('checkForUpdate completes without throwing when unsupported', () async {
      expect(updateService.checkForUpdate(), completes);
    });
  });

  group('AppReviewService Tests', () {
    final reviewService = AppReviewService.instance;

    test('isSupported is safely false in Flutter test environment', () {
      expect(reviewService.isSupported, isFalse);
    });

    test('requestReview completes safely without throwing', () async {
      expect(reviewService.requestReview(), completes);
    });

    test('openStoreListing completes safely without throwing', () async {
      expect(reviewService.openStoreListing(), completes);
    });
  });

  group('ProfileScreen Rate & Review Tile Tests', () {
    testWidgets('Renders Rate & Review App tile in Profile Screen', (
      WidgetTester tester,
    ) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: ProfileScreen(),
        ),
      );

      await tester.pump();

      expect(find.text('Rate & Review App'), findsOneWidget);
      expect(find.byIcon(Icons.star_rounded), findsOneWidget);
    });
  });
}
