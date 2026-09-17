import 'dart:convert';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;

import 'api_client.dart';
import 'auth_service.dart';
import 'notification_navigation.dart';

/// Must be a top-level (or static) function per firebase_messaging's
/// requirements. Left empty deliberately: our backend sends FCM
/// "notification" messages (not data-only), so the OS already displays a
/// tray notification for a backgrounded/terminated app on its own — this
/// handler only needs to exist for Firebase to dispatch to the plugin.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {}

/// Registers this device's FCM token with the backend, shows a rich local
/// notification while the app is in the foreground (push_notification_prd.md
/// §10 — the OS only auto-displays a tray notification when backgrounded or
/// terminated), and deep-links a tapped notification via
/// [openNotificationDestination] regardless of which of those three states
/// it was tapped from.
class PushNotificationService {
  PushNotificationService._();
  static final PushNotificationService instance = PushNotificationService._();

  /// Shared with QuizsApp's MaterialApp so a notification tap can navigate
  /// without needing a BuildContext of its own.
  static final navigatorKey = GlobalKey<NavigatorState>();

  final _localNotifications = FlutterLocalNotificationsPlugin();
  bool _initialized = false;
  int _nextLocalNotificationId = 0;

  Future<void> initialize() async {
    if (_initialized || Firebase.apps.isEmpty) return;
    _initialized = true;

    try {
      await _localNotifications.initialize(
        settings: const InitializationSettings(
          android: AndroidInitializationSettings('@mipmap/ic_launcher'),
          iOS: DarwinInitializationSettings(),
        ),
        onDidReceiveNotificationResponse: _handleLocalNotificationTap,
      );

      FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
      await FirebaseMessaging.instance.requestPermission();
    } catch (e) {
      debugPrint('Push notification setup note: $e');
    }

    AuthService.instance.addListener(_onAuthChanged);
    _onAuthChanged();

    FirebaseMessaging.instance.onTokenRefresh.listen((_) => _registerToken());
    FirebaseMessaging.onMessage.listen(_showForegroundNotification);
    FirebaseMessaging.onMessageOpenedApp.listen(_handleRemoteNotificationTap);

    try {
      final initialMessage = await FirebaseMessaging.instance.getInitialMessage();
      if (initialMessage != null) _handleRemoteNotificationTap(initialMessage);
    } catch (e) {
      debugPrint('Initial push message note: $e');
    }
  }

  void _onAuthChanged() {
    if (AuthService.instance.isLoggedIn && !AuthService.instance.mustChangePassword) {
      _registerToken();
    }
  }

  Future<void> _registerToken() async {
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token == null) return;
      await ApiClient.instance.post('/device/fcm-token', body: {
        'token': token,
        'platform': defaultTargetPlatform.name,
      });
    } catch (e) {
      debugPrint('FCM token registration note: $e');
    }
  }

  Future<void> _showForegroundNotification(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    final imageUrl = notification.android?.imageUrl ?? notification.apple?.imageUrl;
    final bigPicture = await _downloadImage(imageUrl);

    final details = NotificationDetails(
      android: AndroidNotificationDetails(
        'push_notifications',
        'Notifications',
        importance: Importance.high,
        priority: Priority.high,
        styleInformation: bigPicture != null ? BigPictureStyleInformation(ByteArrayAndroidBitmap(bigPicture)) : null,
      ),
      iOS: const DarwinNotificationDetails(),
    );

    await _localNotifications.show(
      id: _nextLocalNotificationId++,
      title: notification.title,
      body: notification.body,
      notificationDetails: details,
      payload: jsonEncode(message.data),
    );
  }

  /// Roadmap PRD §16: an image that fails to download must not prevent the
  /// notification from showing — it just falls back to text-only.
  Future<Uint8List?> _downloadImage(String? url) async {
    if (url == null) return null;
    try {
      final response = await http.get(Uri.parse(url)).timeout(const Duration(seconds: 8));
      return response.statusCode == 200 ? response.bodyBytes : null;
    } catch (_) {
      return null;
    }
  }

  void _handleLocalNotificationTap(NotificationResponse response) {
    final payload = response.payload;
    if (payload == null || payload.isEmpty) return;
    try {
      _navigateToDestination((jsonDecode(payload) as Map).cast<String, dynamic>());
    } catch (_) {}
  }

  void _handleRemoteNotificationTap(RemoteMessage message) => _navigateToDestination(message.data);

  void _navigateToDestination(Map<String, dynamic> data) {
    final context = navigatorKey.currentContext;
    if (context == null) return;
    openNotificationDestination(
      context,
      destinationType: data['destination_type'] as String? ?? 'none',
      destinationId: int.tryParse('${data['destination_id']}'),
    );
  }
}
