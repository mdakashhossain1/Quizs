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
    if (!UnityAdsService.instance.isSupported || !UnityAdsService.instance.isInitialized || inTest) {
      // In tests, non-Android, or before initialization
      adContent = Container(
        color: Colors.white,
        alignment: Alignment.center,
        child: const Text(
          'Unity Ads (Android only)',
          style: TextStyle(fontSize: 10, color: Color(0xFF777777)),
        ),
      );
    } else if (_failed) {
      adContent = Container(
        color: Colors.white,
        alignment: Alignment.center,
        child: TextButton(
          onPressed: () => setState(() => _failed = false),
          child: const Text(
            'Ad unavailable. Tap to retry',
            style: TextStyle(fontSize: 10),
          ),
        ),
      );
    } else {
      adContent = UnityBannerAd(
        placementId: UnityAdsService.bannerPlacementId,
        onLoad: (placementId) {
          debugPrint('Unity Banner loaded: $placementId');
        },
        onFailed: (placementId, error, message) {
          debugPrint('Unity Banner failed: $placementId - $error: $message');
          if (mounted) setState(() => _failed = true);
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
