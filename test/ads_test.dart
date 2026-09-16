import 'package:flutter_test/flutter_test.dart';
import 'package:quizs/ads/test_ads.dart';

void main() {
  group('TestAds Unit Tests', () {
    test('Ad unit IDs are configured properly', () {
      expect(TestAds.bannerId, isNotEmpty);
      expect(TestAds.appOpenId, isNotEmpty);
      expect(TestAds.interstitialId, isNotEmpty);
    });

    test('showInterstitialAd gracefully executes callback when ads are disabled or unsupported', () {
      var completed = false;
      TestAds.showInterstitialAd(
        onComplete: () {
          completed = true;
        },
      );
      expect(completed, isTrue);
    });

    test('showAppOpenAdIfAvailable does not crash when uninitialized or disabled', () {
      expect(() => TestAds.showAppOpenAdIfAvailable(), returnsNormally);
    });
  });
}
