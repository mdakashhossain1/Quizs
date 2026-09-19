import 'dart:async';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:unity_ads_plugin/unity_ads_plugin.dart';

import '../l10n/app_strings.dart';
import '../services/unity_ads_service.dart';

class UnityBannerSlot extends StatefulWidget {
  const UnityBannerSlot({super.key, this.isDark});

  final bool? isDark;

  static const double width = 320;
  static const double adHeight = 50;
  static const double labelHeight = 16;
  static const double height = adHeight + labelHeight;

  @override
  State<UnityBannerSlot> createState() => _UnityBannerSlotState();
}

class _UnityBannerSlotState extends State<UnityBannerSlot> {
  bool _failed = false;
  String? _lastError;
  String _activePlacement = UnityAdsService.bannerPlacementId;
  Timer? _retryTimer;

  @override
  void initState() {
    super.initState();
    UnityAdsService.instance.addListener(_onServiceUpdate);
  }

  @override
  void dispose() {
    UnityAdsService.instance.removeListener(_onServiceUpdate);
    _retryTimer?.cancel();
    super.dispose();
  }

  void _onServiceUpdate() {
    if (mounted) {
      setState(() {});
    }
  }

  void _retryBanner() {
    _retryTimer?.cancel();
    if (mounted) {
      setState(() {
        _failed = false;
        _lastError = null;
        _activePlacement = UnityAdsService.bannerPlacementId;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.isDark ??
        (ThemeData.estimateBrightnessForColor(Theme.of(context).canvasColor) ==
            Brightness.dark);
    final labelColor = isDark
        ? const Color(0xB3FFFFFF)
        : const Color(0xFF757575);

    final advertisementLabel = SizedBox(
      height: UnityBannerSlot.labelHeight,
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
    final service = UnityAdsService.instance;

    if (!service.isSupported || inTest) {
      adContent = Container(
        color: isDark ? const Color(0xFF2C2C2E) : const Color(0xFFF2F2F7),
        alignment: Alignment.center,
        child: Text(
          'Unity Ads (Android only)',
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
                'Unity Init: ${service.lastError ?? "Failed"}',
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
      adContent = UnityBannerAd(
        placementId: _activePlacement,
        onLoad: (placementId) {
          debugPrint('Unity Banner loaded successfully: $placementId');
          if (_failed && mounted) {
            setState(() {
              _failed = false;
              _lastError = null;
            });
          }
        },
        onFailed: (placementId, error, message) {
          debugPrint('Unity Banner failed: $placementId - $error: $message');
          if (mounted) {
            // If primary Banner_Android failed, try fallback banner placement
            if (placementId == UnityAdsService.bannerPlacementId &&
                _activePlacement == UnityAdsService.bannerPlacementId) {
              setState(() {
                _activePlacement = UnityAdsService.fallbackBannerPlacementId;
                _failed = false;
              });
              return;
            }

            setState(() {
              _failed = true;
              _lastError = '$error';
            });

            // Automatically retry after 8 seconds
            _retryTimer?.cancel();
            _retryTimer = Timer(const Duration(seconds: 8), () {
              if (mounted && _failed) {
                setState(() => _failed = false);
              }
            });
          }
        },
        onClick: (placementId) {
          debugPrint('Unity Banner clicked: $placementId');
        },
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth < UnityBannerSlot.width) {
          return Center(
            child: SizedBox(
              height: UnityBannerSlot.height,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  advertisementLabel,
                  SizedBox(
                    height: UnityBannerSlot.adHeight,
                    child: adContent,
                  ),
                ],
              ),
            ),
          );
        }
        return Center(
          child: SizedBox(
            width: UnityBannerSlot.width,
            height: UnityBannerSlot.height,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                advertisementLabel,
                SizedBox(
                  width: UnityBannerSlot.width,
                  height: UnityBannerSlot.adHeight,
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
