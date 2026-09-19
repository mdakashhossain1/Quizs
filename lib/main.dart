import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'quizs_app.dart';
import 'services/activity_service.dart';
import 'services/auth_service.dart';
import 'services/push_notification_service.dart';
import 'services/unity_ads_service.dart';
import 'firebase_options.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);

  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    FlutterError.onError = FirebaseCrashlytics.instance.recordFlutterFatalError;
    PlatformDispatcher.instance.onError = (error, stack) {
      FirebaseCrashlytics.instance.recordError(error, stack, fatal: true);
      return true;
    };
    await FirebaseCrashlytics.instance.setCrashlyticsCollectionEnabled(!kDebugMode);
  } catch (e) {
    debugPrint('Firebase.initializeApp note: $e');
  }

  await AuthService.instance.initialize();
  ActivityService.instance.initialize();
  PushNotificationService.instance.initialize();
  UnityAdsService.instance.initialize();
  runApp(const QuizsApp());
}
