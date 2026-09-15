import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

abstract final class TestAds {
  static Future<void>? _initialization;
  static bool _started = false;

  static bool get supported =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.android ||
          defaultTargetPlatform == TargetPlatform.iOS);

  static bool get enabled => supported && _started;

  static String get bannerId => defaultTargetPlatform == TargetPlatform.iOS
      ? 'ca-app-pub-3940256099942544/2934735716'
      : 'ca-app-pub-3940256099942544/6300978111';

  static void start() {
    if (!supported) return;
    _started = true;
    unawaited(
      ensureInitialized().catchError((Object error) {
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
}
