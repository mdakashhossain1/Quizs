import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

class TestAds with WidgetsBindingObserver {
  TestAds._();
  static final TestAds instance = TestAds._();

  static Future<void>? _initialization;
  static bool _started = false;
  static bool _isShowingFullScreenAd = false;
  static bool _hasShownColdStartAppOpenAd = false;

  // App Open Ad
  static AppOpenAd? _appOpenAd;
  static DateTime? _appOpenLoadTime;
  static bool _isLoadingAppOpen = false;

  // Interstitial Ad
  static InterstitialAd? _interstitialAd;
  static bool _isLoadingInterstitial = false;

  // On Android, `resumed` also fires after a transient `inactive` blip that
  // never actually left the foreground (keyboard open/close, permission
  // dialogs, other window-focus changes) — only treat `resumed` as an app
  // open when it follows a real `paused`, so in-app navigation/typing
  // doesn't retrigger the App Open ad.
  static bool _wasBackgrounded = false;

  static bool get supported =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.android ||
          defaultTargetPlatform == TargetPlatform.iOS);

  static bool get enabled => supported && _started;

  static String get bannerId => defaultTargetPlatform == TargetPlatform.iOS
      ? 'ca-app-pub-3940256099942544/2934735716'
      : 'ca-app-pub-3940256099942544/6300978111';

  static String get appOpenId => defaultTargetPlatform == TargetPlatform.iOS
      ? 'ca-app-pub-3940256099942544/5575463023'
      : 'ca-app-pub-3940256099942544/9257395921';

  static String get interstitialId => defaultTargetPlatform == TargetPlatform.iOS
      ? 'ca-app-pub-3940256099942544/4411468910'
      : 'ca-app-pub-3940256099942544/1033173712';

  /// Initializes AdMob and prepares App Open Ads and Interstitial Ads.
  static void start() {
    if (!supported) return;
    _started = true;
    WidgetsBinding.instance.addObserver(instance);
    unawaited(
      ensureInitialized().then((_) {
        // When app is opened (cold start), load and show App Open Ad
        loadAppOpenAd(showWhenLoaded: true);
        // Preload Interstitial Ad for quiz questions
        loadInterstitialAd();
      }).catchError((Object error) {
        debugPrint('AdMob initialization failed: $error');
      }),
    );
  }

  static Future<void> ensureInitialized() async {
    final pending = _initialization ??= MobileAds.instance.initialize().then(
      (_) {},
    );
    try {
      await pending;
    } catch (_) {
      if (identical(_initialization, pending)) _initialization = null;
      rethrow;
    }
  }

  // ==========================================
  // --- APP OPEN ADS (Only on App Open/Resume) ---
  // ==========================================
  static bool get isAppOpenAdAvailable {
    if (_appOpenAd == null || _appOpenLoadTime == null) return false;
    // App Open Ads expire after 4 hours per Google guidelines
    return DateTime.now().difference(_appOpenLoadTime!) < const Duration(hours: 4);
  }

  static void loadAppOpenAd({bool showWhenLoaded = false}) {
    if (!enabled || _isLoadingAppOpen || isAppOpenAdAvailable) return;
    _isLoadingAppOpen = true;
    AppOpenAd.load(
      adUnitId: appOpenId,
      request: const AdRequest(nonPersonalizedAds: true),
      adLoadCallback: AppOpenAdLoadCallback(
        onAdLoaded: (ad) {
          _appOpenAd = ad;
          _appOpenLoadTime = DateTime.now();
          _isLoadingAppOpen = false;
          debugPrint('AdMob App Open Ad loaded successfully.');
          if (showWhenLoaded && !_hasShownColdStartAppOpenAd) {
            _hasShownColdStartAppOpenAd = true;
            showAppOpenAdIfAvailable();
          }
        },
        onAdFailedToLoad: (error) {
          _appOpenAd = null;
          _isLoadingAppOpen = false;
          debugPrint('AdMob App Open Ad failed to load: ${error.message}');
        },
      ),
    );
  }

  /// Shows the App Open Ad when the user opens the app or resumes from background.
  static void showAppOpenAdIfAvailable() {
    if (!enabled || _isShowingFullScreenAd) return;
    if (!isAppOpenAdAvailable) {
      loadAppOpenAd();
      return;
    }

    final ad = _appOpenAd;
    if (ad == null) return;
    _isShowingFullScreenAd = true;
    ad.fullScreenContentCallback = FullScreenContentCallback(
      onAdShowedFullScreenContent: (ad) {
        debugPrint('AdMob App Open Ad showed on app opening.');
      },
      onAdDismissedFullScreenContent: (ad) {
        debugPrint('AdMob App Open Ad dismissed.');
        _isShowingFullScreenAd = false;
        ad.dispose();
        _appOpenAd = null;
        loadAppOpenAd();
      },
      onAdFailedToShowFullScreenContent: (ad, error) {
        debugPrint('AdMob App Open Ad failed to show: ${error.message}');
        _isShowingFullScreenAd = false;
        ad.dispose();
        _appOpenAd = null;
        loadAppOpenAd();
      },
    );
    ad.show();
    _appOpenAd = null;
  }

  // ==========================================
  // --- INTERSTITIAL ADS (Quiz Questions) ---
  // ==========================================
  static bool get isInterstitialAdAvailable => _interstitialAd != null;

  static void loadInterstitialAd() {
    if (!enabled || _isLoadingInterstitial || _interstitialAd != null) return;
    _isLoadingInterstitial = true;
    InterstitialAd.load(
      adUnitId: interstitialId,
      request: const AdRequest(nonPersonalizedAds: true),
      adLoadCallback: InterstitialAdLoadCallback(
        onAdLoaded: (ad) {
          _interstitialAd = ad;
          _isLoadingInterstitial = false;
          debugPrint('AdMob Interstitial Ad loaded successfully.');
        },
        onAdFailedToLoad: (error) {
          _interstitialAd = null;
          _isLoadingInterstitial = false;
          debugPrint('AdMob Interstitial Ad failed to load: ${error.message}');
        },
      ),
    );
  }

  /// Shows Interstitial Ad during quiz gameplay (e.g. every 2 questions).
  static void showInterstitialAd({VoidCallback? onComplete}) {
    if (!enabled || _interstitialAd == null || _isShowingFullScreenAd) {
      onComplete?.call();
      if (enabled && _interstitialAd == null) {
        loadInterstitialAd();
      }
      return;
    }

    final ad = _interstitialAd!;
    _interstitialAd = null;
    _isShowingFullScreenAd = true;

    ad.fullScreenContentCallback = FullScreenContentCallback(
      onAdShowedFullScreenContent: (ad) {
        debugPrint('AdMob Interstitial Ad showed full screen.');
      },
      onAdDismissedFullScreenContent: (ad) {
        debugPrint('AdMob Interstitial Ad dismissed.');
        _isShowingFullScreenAd = false;
        ad.dispose();
        loadInterstitialAd();
        onComplete?.call();
      },
      onAdFailedToShowFullScreenContent: (ad, error) {
        debugPrint('AdMob Interstitial Ad failed to show: ${error.message}');
        _isShowingFullScreenAd = false;
        ad.dispose();
        loadInterstitialAd();
        onComplete?.call();
      },
    );
    ad.show();
  }

  /// Triggered whenever the user brings the app back to foreground.
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused) {
      _wasBackgrounded = true;
    } else if (state == AppLifecycleState.resumed) {
      if (_wasBackgrounded) {
        _wasBackgrounded = false;
        showAppOpenAdIfAvailable();
      }
    }
  }
}
