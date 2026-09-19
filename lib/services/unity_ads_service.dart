import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:unity_ads_plugin/unity_ads_plugin.dart';

class UnityAdsService extends ChangeNotifier {
  UnityAdsService._();
  static final UnityAdsService instance = UnityAdsService._();

  // Unity Ads Game ID for Android
  static const String androidGameId = '800377165';
  static const String interstitialPlacementId = 'BP_Interstitial_Android';
  static const String bannerPlacementId = 'BP_Banner_Android';
  static const String rewardedPlacementId = 'BP_Rewarded_Android';
  static const String fallbackInterstitialPlacementId = 'Interstitial_Android';
  static const String fallbackBannerPlacementId = 'Banner_Android';

  bool _isInitialized = false;
  bool _initFailed = false;
  String? _lastError;
  String? _lastInterstitialError;
  bool _isLoadingAd = false;
  bool _isAdLoaded = false;
  bool _testMode = false;
  String _activeInterstitialPlacement = interstitialPlacementId;
  Timer? _interstitialRetryTimer;
  Timer? _initRetryTimer;

  /// Unity Ads in this app is restricted solely to Android
  bool get isSupported =>
      !kIsWeb && defaultTargetPlatform == TargetPlatform.android;

  bool get isInitialized => _isInitialized;
  bool get initFailed => _initFailed;
  String? get lastError => _lastError;
  bool get isAdLoaded => _isAdLoaded;
  bool get isLoadingAd => _isLoadingAd;
  bool get testMode => _testMode;

  /// Initializes the Unity Ads SDK if running on Android.
  /// [testMode] is set to false for live real production ads.
  Future<void> initialize({
    String gameId = androidGameId,
    bool testMode = false,
  }) async {
    if (!isSupported || _isInitialized) return;

    _testMode = testMode;
    _initFailed = false;
    _lastError = null;

    try {
      await UnityAds.init(
        gameId: gameId,
        testMode: _testMode,
        onComplete: () {
          _isInitialized = true;
          _initFailed = false;
          _initRetryTimer?.cancel();
          debugPrint('Unity Ads initialized successfully (Game ID: $gameId, testMode: $_testMode).');
          notifyListeners();
          loadInterstitialAd();
        },
        onFailed: (error, message) {
          _isInitialized = false;
          _initFailed = true;
          _lastError = '$error: $message';
          debugPrint('Unity Ads initialization failed: $error - $message');
          notifyListeners();

          // Auto retry init after 5 seconds in case network was not ready
          _initRetryTimer?.cancel();
          _initRetryTimer = Timer(const Duration(seconds: 5), () {
            if (!_isInitialized && isSupported) {
              initialize(gameId: gameId, testMode: testMode);
            }
          });
        },
      );
    } catch (e) {
      _initFailed = true;
      _lastError = e.toString();
      debugPrint('Unity Ads initialization note: $e');
      notifyListeners();
    }
  }

  String? get lastInterstitialError => _lastInterstitialError;

  /// Preloads the interstitial ad in the background.
  Future<void> loadInterstitialAd({
    String? placementId,
  }) async {
    final targetPlacement = placementId ?? _activeInterstitialPlacement;
    if (!isSupported || !_isInitialized || _isLoadingAd || _isAdLoaded) return;

    _isLoadingAd = true;
    notifyListeners();

    try {
      await UnityAds.load(
        placementId: targetPlacement,
        onComplete: (loadedId) {
          _isLoadingAd = false;
          _isAdLoaded = true;
          _activeInterstitialPlacement = loadedId;
          _lastInterstitialError = null;
          _interstitialRetryTimer?.cancel();
          debugPrint('Unity Ads interstitial loaded: $loadedId');
          notifyListeners();
        },
        onFailed: (failedId, error, message) {
          _isLoadingAd = false;
          _isAdLoaded = false;
          _lastInterstitialError = '$error: $message';
          debugPrint('Unity Ads load failed: $failedId - $error: $message');
          notifyListeners();

          // If primary placement failed with invalid argument or unknown, try fallback placement
          if (failedId == interstitialPlacementId &&
              _activeInterstitialPlacement == interstitialPlacementId) {
            _activeInterstitialPlacement = fallbackInterstitialPlacementId;
            loadInterstitialAd(placementId: fallbackInterstitialPlacementId);
            return;
          }

          // Auto retry loading interstitial after 8 seconds
          _interstitialRetryTimer?.cancel();
          _interstitialRetryTimer = Timer(const Duration(seconds: 8), () {
            if (_isInitialized && !_isAdLoaded && !_isLoadingAd) {
              loadInterstitialAd();
            }
          });
        },
      );
    } catch (e) {
      _isLoadingAd = false;
      _isAdLoaded = false;
      _lastInterstitialError = e.toString();
      debugPrint('Unity Ads load note: $e');
      notifyListeners();
    }
  }

  /// Shows the cached interstitial ad.
  /// If unsupported, uninitialized, or ad is not loaded, invokes [onComplete] immediately
  /// and triggers a background preload for subsequent questions.
  void showInterstitialAd({
    VoidCallback? onComplete,
    String? placementId,
  }) {
    final targetPlacement = placementId ?? _activeInterstitialPlacement;
    if (!isSupported || !_isInitialized || !_isAdLoaded) {
      onComplete?.call();
      if (isSupported && _isInitialized && !_isAdLoaded && !_isLoadingAd) {
        loadInterstitialAd(placementId: targetPlacement);
      }
      return;
    }

    _isAdLoaded = false;
    notifyListeners();

    var hasCompleted = false;
    void safeComplete() {
      if (!hasCompleted) {
        hasCompleted = true;
        onComplete?.call();
        loadInterstitialAd(placementId: targetPlacement);
      }
    }

    try {
      UnityAds.showVideoAd(
        placementId: targetPlacement,
        onComplete: (placementId) => safeComplete(),
        onFailed: (placementId, error, message) {
          debugPrint('Unity Ads show failed: $placementId - $error: $message');
          safeComplete();
        },
        onSkipped: (placementId) => safeComplete(),
      );
    } catch (e) {
      debugPrint('Unity Ads show note: $e');
      safeComplete();
    }
  }
}
