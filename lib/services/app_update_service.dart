import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:in_app_update/in_app_update.dart';

/// Manages Google Play In-App Updates (checking, downloading, and completing updates).
class AppUpdateService {
  AppUpdateService._();
  static final AppUpdateService instance = AppUpdateService._();

  bool _isChecking = false;
  AppUpdateInfo? _updateInfo;

  AppUpdateInfo? get updateInfo => _updateInfo;
  bool get isChecking => _isChecking;

  bool get isSupported =>
      !kIsWeb &&
      defaultTargetPlatform == TargetPlatform.android &&
      !Platform.environment.containsKey('FLUTTER_TEST');

  /// Checks Google Play Store for available app updates on Android.
  /// If a flexible update is available, initiates the native Google Play sheet.
  Future<void> checkForUpdate() async {
    if (!isSupported || _isChecking) return;

    _isChecking = true;
    try {
      final info = await InAppUpdate.checkForUpdate();
      _updateInfo = info;

      if (info.updateAvailability == UpdateAvailability.updateAvailable) {
        debugPrint(
          'In-App Update available: availableVersionCode=${info.availableVersionCode}',
        );

        if (info.flexibleUpdateAllowed) {
          // Flexible background update
          final result = await InAppUpdate.startFlexibleUpdate();
          debugPrint('In-App Flexible update result: $result');

          // Once downloaded, complete update
          await InAppUpdate.completeFlexibleUpdate();
        } else if (info.immediateUpdateAllowed) {
          // Immediate update flow
          await InAppUpdate.performImmediateUpdate();
        }
      } else {
        debugPrint('In-App Update: App is up to date.');
      }
    } catch (e) {
      // Normal on local debug builds, emulators, or sideloaded APKs
      debugPrint('In-App Update check note: $e');
    } finally {
      _isChecking = false;
    }
  }
}
