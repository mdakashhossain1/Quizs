import 'dart:async';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:unity_levelplay_mediation/unity_levelplay_mediation.dart';

import '../l10n/app_strings.dart';
import '../services/iron_source_service.dart';

class IronSourceBannerSlot extends StatefulWidget {
  const IronSourceBannerSlot({super.key, this.isDark});

  final bool? isDark;

  static const double width = 320;
  static const double adHeight = 50;
  static const double labelHeight = 16;
  static const double height = adHeight + labelHeight;

  @override
  State<IronSourceBannerSlot> createState() => _IronSourceBannerSlotState();
}

class _IronSourceBannerSlotState extends State<IronSourceBannerSlot>
    with LevelPlayBannerAdViewListener {
  final GlobalKey<LevelPlayBannerAdViewState> _bannerKey =
      GlobalKey<LevelPlayBannerAdViewState>();

  bool _failed = false;
  String? _lastError;
  bool _isAdLoaded = false;
  Timer? _retryTimer;

  @override
  void initState() {
    super.initState();
    IronSourceService.instance.addListener(_onServiceUpdate);
  }

  @override
  void dispose() {
    IronSourceService.instance.removeListener(_onServiceUpdate);
    _retryTimer?.cancel();
    super.dispose();
  }

  void _onServiceUpdate() {
    if (mounted) {
      setState(() {});
      if (IronSourceService.instance.isInitialized && !_isAdLoaded && !_failed) {
        _loadBanner();
      }
    }
  }

  void _loadBanner() {
    try {
      _bannerKey.currentState?.loadAd();
    } catch (e) {
      debugPrint('Error loading LevelPlay banner: $e');
    }
  }

  void _retryBanner() {
    _retryTimer?.cancel();
    if (mounted) {
      setState(() {
        _failed = false;
        _lastError = null;
      });
      _loadBanner();
    }
  }

  // --- LevelPlayBannerAdViewListener Callbacks ---

  @override
  void onAdLoaded(LevelPlayAdInfo adInfo) {
    debugPrint('LevelPlay Banner loaded: ${adInfo.adUnitId}');
    if (mounted) {
      setState(() {
        _isAdLoaded = true;
        _failed = false;
        _lastError = null;
      });
    }
  }

  @override
  void onAdLoadFailed(LevelPlayAdError error) {
    debugPrint(
      'LevelPlay Banner load failed: ${error.errorCode} - ${error.errorMessage}',
    );
    if (mounted) {
      setState(() {
        _failed = true;
        _lastError = error.errorMessage;
      });

      _retryTimer?.cancel();
      _retryTimer = Timer(const Duration(seconds: 15), () {
        if (mounted && _failed) {
          _retryBanner();
        }
      });
    }
  }

  @override
  void onAdDisplayed(LevelPlayAdInfo adInfo) {
    debugPrint('LevelPlay Banner displayed: ${adInfo.adUnitId}');
  }

  @override
  void onAdDisplayFailed(LevelPlayAdInfo adInfo, LevelPlayAdError error) {
    debugPrint(
      'LevelPlay Banner display failed: ${error.errorCode} - ${error.errorMessage}',
    );
  }

  @override
  void onAdClicked(LevelPlayAdInfo adInfo) {
    debugPrint('LevelPlay Banner clicked: ${adInfo.adUnitId}');
  }

  @override
  void onAdExpanded(LevelPlayAdInfo adInfo) {
    debugPrint('LevelPlay Banner expanded: ${adInfo.adUnitId}');
  }

  @override
  void onAdCollapsed(LevelPlayAdInfo adInfo) {
    debugPrint('LevelPlay Banner collapsed: ${adInfo.adUnitId}');
  }

  @override
  void onAdLeftApplication(LevelPlayAdInfo adInfo) {
    debugPrint('LevelPlay Banner left application: ${adInfo.adUnitId}');
  }

  @override
  Widget build(BuildContext context) {
    final isDark =
        widget.isDark ??
        (ThemeData.estimateBrightnessForColor(Theme.of(context).canvasColor) ==
            Brightness.dark);
    final labelColor =
        isDark ? const Color(0xB3FFFFFF) : const Color(0xFF757575);

    final advertisementLabel = SizedBox(
      height: IronSourceBannerSlot.labelHeight,
      child: Center(
        child: Text(
          AppStrings.t('advertisement'),
          style: TextStyle(
            fontFamily: 'Poppins',
            fontSize: 9,
            fontWeight: FontWeight.w600,
            letterSpacing: 1.0,
            height: 1.0,
            color: labelColor,
          ),
        ),
      ),
    );

    Widget adContent;
    final inTest = !kIsWeb && Platform.environment.containsKey('FLUTTER_TEST');
    final service = IronSourceService.instance;

    if (!service.isSupported || inTest) {
      adContent = Container(
        color: isDark ? const Color(0xFF2C2C2E) : const Color(0xFFF2F2F7),
        alignment: Alignment.center,
        child: Text(
          'IronSource Ads (Mobile only)',
          style: TextStyle(
            fontSize: 10,
            color: isDark ? const Color(0xFFAAAAAA) : const Color(0xFF777777),
          ),
        ),
      );
    } else if (!service.isInitialized) {
      if (service.initFailed) {
        adContent = Container(
          color: isDark ? const Color(0xFF2C2C2E) : const Color(0xFFF2F2F7),
          alignment: Alignment.center,
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                'IronSource Init: ${service.lastError ?? "Failed"}',
                style: TextStyle(
                  fontSize: 9,
                  color: isDark ? Colors.redAccent : Colors.red,
                ),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              InkWell(
                onTap: () => service.initialize(),
                child: const Text(
                  'Tap to retry init',
                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        );
      } else {
        adContent = Container(
          color: isDark ? const Color(0xFF2C2C2E) : const Color(0xFFF2F2F7),
          alignment: Alignment.center,
          child: SizedBox(
            width: 16,
            height: 16,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: isDark ? Colors.white54 : Colors.grey,
            ),
          ),
        );
      }
    } else if (_failed) {
      adContent = Container(
        color: isDark ? const Color(0xFF2C2C2E) : const Color(0xFFF2F2F7),
        alignment: Alignment.center,
        padding: const EdgeInsets.symmetric(horizontal: 8),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              _lastError != null ? 'Ad: $_lastError' : 'Ad unavailable',
              style: TextStyle(
                fontSize: 9,
                color: isDark ? Colors.white70 : const Color(0xFF555555),
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 2),
            InkWell(
              onTap: _retryBanner,
              child: Text(
                'Tap to retry',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  color: Theme.of(context).colorScheme.primary,
                ),
              ),
            ),
          ],
        ),
      );
    } else {
      adContent = LevelPlayBannerAdView(
        key: _bannerKey,
        adUnitId: IronSourceService.bannerAdUnitId,
        adSize: LevelPlayAdSize.BANNER,
        listener: this,
        onPlatformViewCreated: () {
          _loadBanner();
        },
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth < IronSourceBannerSlot.width) {
          return Center(
            child: SizedBox(
              height: IronSourceBannerSlot.height,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  advertisementLabel,
                  SizedBox(
                    height: IronSourceBannerSlot.adHeight,
                    child: adContent,
                  ),
                ],
              ),
            ),
          );
        }
        return Center(
          child: SizedBox(
            width: IronSourceBannerSlot.width,
            height: IronSourceBannerSlot.height,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                advertisementLabel,
                SizedBox(
                  width: IronSourceBannerSlot.width,
                  height: IronSourceBannerSlot.adHeight,
                  child: adContent,
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
