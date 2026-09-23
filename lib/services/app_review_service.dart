import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:in_app_review/in_app_review.dart';

/// Service managing native In-App Reviews and Play Store review links.
class AppReviewService {
  AppReviewService._();
  static final AppReviewService instance = AppReviewService._();

  final InAppReview _inAppReview = InAppReview.instance;

  bool get isSupported =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.android ||
          defaultTargetPlatform == TargetPlatform.iOS) &&
      !Platform.environment.containsKey('FLUTTER_TEST');

  /// Requests the native in-app review dialog from Google Play.
  /// If in-app review is unavailable or quota is exceeded, falls back to opening
  /// the Google Play Store listing directly so the user can still leave a review.
  Future<void> requestReview() async {
    if (!isSupported) {
      debugPrint('AppReviewService: not supported on this platform/environment.');
      return;
    }

    try {
      final isAvailable = await _inAppReview.isAvailable();
      if (isAvailable) {
        await _inAppReview.requestReview();
      } else {
        await _inAppReview.openStoreListing();
      }
    } catch (e) {
      debugPrint('AppReviewService requestReview exception: $e');
      try {
        await _inAppReview.openStoreListing();
      } catch (inner) {
        debugPrint('AppReviewService openStoreListing exception: $inner');
      }
    }
  }

  /// Directly opens the app store page to write a review.
  Future<void> openStoreListing() async {
    try {
      await _inAppReview.openStoreListing();
    } catch (e) {
      debugPrint('AppReviewService openStoreListing exception: $e');
    }
  }
}
