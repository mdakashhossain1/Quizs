import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:unity_ads_plugin/unity_ads_plugin.dart';

class UnityAdsService {
  UnityAdsService._();
  static final UnityAdsService instance = UnityAdsService._();

  // Standard test Game ID for Unity Ads on Android
  static const String androidGameId = '5268482';
  static const String interstitialPlacementId = 'Interstitial_Android';
  static const String bannerPlacementId = 'Banner_Android';

  bool _isInitialized = false;
  bool _isLoadingAd = false;
  bool _isAdLoaded = false;
  bool _testMode = true;

  /// Unity Ads in this app is restricted solely to Android
  bool get isSupported =>
      !kIsWeb && defaultTargetPlatform == TargetPlatform.android;

  bool get isInitialized => _isInitialized;
  bool get isAdLoaded => _isAdLoaded;

  /// Initializes the Unity Ads SDK if running on Android.
  Future<void> initialize({
    String gameId = androidGameId,
    bool testMode = true,
  }) async {
    if (!isSupported || _isInitialized) return;

    _testMode = testMode;

    try {
      await UnityAds.init(
        gameId: gameId,
        testMode: _testMode,
        onComplete: () {
          _isInitialized = true;
          debugPrint('Unity Ads initialized successfully (Android).');
          loadInterstitialAd();
        },
        onFailed: (error, message) {
          _isInitialized = false;
          debugPrint('Unity Ads initialization failed: $error - $message');
        },
      );
    } catch (e) {
      debugPrint('Unity Ads initialization note: $e');
    }
  }

  /// Preloads the interstitial ad in the background.
  Future<void> loadInterstitialAd({
    String placementId = interstitialPlacementId,
  }) async {
    if (!isSupported || !_isInitialized || _isLoadingAd || _isAdLoaded) return;

    _isLoadingAd = true;
    try {
      await UnityAds.load(
        placementId: placementId,
        onComplete: (placementId) {
          _isLoadingAd = false;
          _isAdLoaded = true;
          debugPrint('Unity Ads interstitial loaded: $placementId');
        },
        onFailed: (placementId, error, message) {
          _isLoadingAd = false;
          _isAdLoaded = false;
          debugPrint('Unity Ads load failed: $placementId - $error: $message');
        },
      );
    } catch (e) {
      _isLoadingAd = false;
      _isAdLoaded = false;
      debugPrint('Unity Ads load note: $e');
    }
  }

  /// Shows the cached interstitial ad.
  /// If unsupported, uninitialized, or ad is not loaded, invokes [onComplete] immediately.
  void showInterstitialAd({
    VoidCallback? onComplete,
    String placementId = interstitialPlacementId,
  }) {
    if (!isSupported || !_isInitialized || !_isAdLoaded) {
      onComplete?.call();
      if (isSupported && _isInitialized && !_isAdLoaded && !_isLoadingAd) {
        loadInterstitialAd(placementId: placementId);
      }
      return;
    }

    _isAdLoaded = false;
    var hasCompleted = false;
    void safeComplete() {
      if (!hasCompleted) {
        hasCompleted = true;
        onComplete?.call();
        loadInterstitialAd(placementId: placementId);
      }
    }

    try {
      UnityAds.showVideoAd(
        placementId: placementId,
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
