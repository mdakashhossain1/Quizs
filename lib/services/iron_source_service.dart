import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:unity_levelplay_mediation/unity_levelplay_mediation.dart';

/// Singleton service managing IronSource (Unity LevelPlay) initialization,
/// state, and Interstitial ads.
class IronSourceService extends ChangeNotifier
    implements LevelPlayInitListener, LevelPlayInterstitialAdListener {
  IronSourceService._();
  static final IronSourceService instance = IronSourceService._();

  // IronSource credentials
  static const String appKey = '2840c05d5';
  static const String interstitialAdUnitId = 'kfr3nr6bza92d3sh';
  static const String bannerAdUnitId = 'k7qwvl1omq6xdnji';

  bool _isInitialized = false;
  bool _initFailed = false;
  String? _lastError;
  String? _lastInterstitialError;
  bool _isLoadingAd = false;
  bool _isAdLoaded = false;
  Timer? _interstitialRetryTimer;
  Timer? _initRetryTimer;

  LevelPlayInterstitialAd? _interstitialAd;
  VoidCallback? _onInterstitialComplete;

  bool get isSupported =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.android ||
          defaultTargetPlatform == TargetPlatform.iOS);

  bool get isInitialized => _isInitialized;
  bool get initFailed => _initFailed;
  String? get lastError => _lastError;
  bool get isAdLoaded => _isAdLoaded;
  bool get isLoadingAd => _isLoadingAd;
  String? get lastInterstitialError => _lastInterstitialError;

  /// Initializes the LevelPlay SDK with the app key.
  Future<void> initialize() async {
    if (!isSupported || _isInitialized) return;

    _initFailed = false;
    _lastError = null;

    try {
      final initRequest = LevelPlayInitRequest.builder(appKey).build();
      await LevelPlay.init(initRequest: initRequest, initListener: this);
    } catch (e) {
      _initFailed = true;
      _lastError = e.toString();
      debugPrint('IronSource LevelPlay init exception: $e');
      notifyListeners();

      _scheduleInitRetry();
    }
  }

  void _scheduleInitRetry() {
    _initRetryTimer?.cancel();
    _initRetryTimer = Timer(const Duration(seconds: 10), () {
      if (!_isInitialized && isSupported) {
        initialize();
      }
    });
  }

  // --- LevelPlayInitListener Callbacks ---

  @override
  void onInitSuccess(LevelPlayConfiguration configuration) {
    _isInitialized = true;
    _initFailed = false;
    _lastError = null;
    _initRetryTimer?.cancel();
    debugPrint('IronSource LevelPlay initialized successfully.');
    notifyListeners();

    _setupInterstitialAd();
    loadInterstitialAd();
  }

  @override
  void onInitFailed(LevelPlayInitError error) {
    _isInitialized = false;
    _initFailed = true;
    _lastError = '${error.errorCode}: ${error.errorMessage}';
    debugPrint('IronSource LevelPlay initialization failed: $_lastError');
    notifyListeners();

    _scheduleInitRetry();
  }

  // --- Interstitial Ad Lifecycle ---

  void _setupInterstitialAd() {
    _interstitialAd = LevelPlayInterstitialAd(adUnitId: interstitialAdUnitId);
    _interstitialAd?.setListener(this);
  }

  /// Preloads the interstitial ad in the background.
  Future<void> loadInterstitialAd() async {
    if (!isSupported || !_isInitialized || _isLoadingAd || _isAdLoaded) return;

    if (_interstitialAd == null) {
      _setupInterstitialAd();
    }

    _isLoadingAd = true;
    _lastInterstitialError = null;
    notifyListeners();

    try {
      _interstitialAd?.loadAd();
    } catch (e) {
      _isLoadingAd = false;
      _isAdLoaded = false;
      _lastInterstitialError = e.toString();
      debugPrint('IronSource Interstitial load exception: $e');
      notifyListeners();
      _scheduleInterstitialRetry();
    }
  }

  void _scheduleInterstitialRetry() {
    _interstitialRetryTimer?.cancel();
    _interstitialRetryTimer = Timer(const Duration(seconds: 12), () {
      if (_isInitialized && !_isAdLoaded && !_isLoadingAd) {
        loadInterstitialAd();
      }
    });
  }

  /// Displays the cached interstitial ad.
  /// If unsupported, uninitialized, or ad not ready, triggers [onComplete] immediately
  /// and silently preloads in background for future calls.
  Future<void> showInterstitialAd({VoidCallback? onComplete}) async {
    if (!isSupported || !_isInitialized || _interstitialAd == null) {
      onComplete?.call();
      return;
    }

    final isReady = await _interstitialAd!.isAdReady();
    if (!isReady) {
      onComplete?.call();
      if (!_isLoadingAd) {
        loadInterstitialAd();
      }
      return;
    }

    _onInterstitialComplete = onComplete;
    _isAdLoaded = false;
    notifyListeners();

    try {
      _interstitialAd!.showAd();
    } catch (e) {
      debugPrint('IronSource Interstitial show exception: $e');
      _finishInterstitial();
    }
  }

  void _finishInterstitial() {
    final callback = _onInterstitialComplete;
    _onInterstitialComplete = null;
    callback?.call();
    loadInterstitialAd();
  }

  // --- LevelPlayInterstitialAdListener Callbacks ---

  @override
  void onAdLoaded(LevelPlayAdInfo adInfo) {
    debugPrint('IronSource Interstitial loaded: ${adInfo.adUnitId}');
    _isLoadingAd = false;
    _isAdLoaded = true;
    _lastInterstitialError = null;
    _interstitialRetryTimer?.cancel();
    notifyListeners();
  }

  @override
  void onAdLoadFailed(LevelPlayAdError error) {
    debugPrint(
      'IronSource Interstitial load failed: ${error.errorCode} - ${error.errorMessage}',
    );
    _isLoadingAd = false;
    _isAdLoaded = false;
    _lastInterstitialError = '${error.errorCode}: ${error.errorMessage}';
    notifyListeners();
    _scheduleInterstitialRetry();
  }

  @override
  void onAdDisplayed(LevelPlayAdInfo adInfo) {
    debugPrint('IronSource Interstitial displayed');
  }

  @override
  void onAdDisplayFailed(LevelPlayAdError error, LevelPlayAdInfo adInfo) {
    debugPrint(
      'IronSource Interstitial display failed: ${error.errorCode} - ${error.errorMessage}',
    );
    _finishInterstitial();
  }

  @override
  void onAdClicked(LevelPlayAdInfo adInfo) {
    debugPrint('IronSource Interstitial clicked');
  }

  @override
  void onAdClosed(LevelPlayAdInfo adInfo) {
    debugPrint('IronSource Interstitial closed');
    _finishInterstitial();
  }

  @override
  void onAdInfoChanged(LevelPlayAdInfo adInfo) {
    debugPrint('IronSource Interstitial info changed');
  }

  @override
  void dispose() {
    _interstitialRetryTimer?.cancel();
    _initRetryTimer?.cancel();
    super.dispose();
  }
}
