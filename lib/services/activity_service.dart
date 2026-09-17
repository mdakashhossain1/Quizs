import 'dart:async';

import 'package:flutter/widgets.dart';

import 'api_client.dart';
import 'auth_service.dart';
import 'device_id.dart';

/// Pings `/activity/heartbeat` on a timer while the app is in the
/// foreground and signed in, so the backend can derive online/offline status
/// from real activity rather than from persistent-login token validity
/// alone. Stops the timer whenever the app is backgrounded — mobile OSes
/// don't guarantee a killed/backgrounded app can keep running, so a
/// heartbeat should only ever represent genuinely active use.
class ActivityService extends WidgetsBindingObserver {
  ActivityService._();
  static final ActivityService instance = ActivityService._();

  Timer? _timer;
  bool _observing = false;
  bool _sending = false;

  void initialize() {
    AuthService.instance.addListener(_onAuthChanged);
    _onAuthChanged();
  }

  void _onAuthChanged() {
    final shouldRun =
        AuthService.instance.isLoggedIn && !AuthService.instance.mustChangePassword;
    if (shouldRun) {
      _ensureObserving();
      _ensureRunning();
    } else {
      _stop();
    }
  }

  void _ensureObserving() {
    if (_observing) return;
    WidgetsBinding.instance.addObserver(this);
    _observing = true;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (!AuthService.instance.isLoggedIn || AuthService.instance.mustChangePassword) return;
    if (state == AppLifecycleState.resumed) {
      _ensureRunning();
    } else {
      _pauseTimer();
    }
  }

  void _ensureRunning() {
    if (_timer != null) return;
    _sendHeartbeat();
    _scheduleTimer();
  }

  void _scheduleTimer() {
    _timer?.cancel();
    final interval = Duration(seconds: AuthService.instance.heartbeatIntervalSeconds);
    _timer = Timer.periodic(interval, (_) => _sendHeartbeat());
  }

  void _pauseTimer() {
    _timer?.cancel();
    _timer = null;
  }

  void _stop() {
    _pauseTimer();
    if (_observing) {
      WidgetsBinding.instance.removeObserver(this);
      _observing = false;
    }
  }

  Future<void> _sendHeartbeat() async {
    if (_sending) return;
    _sending = true;
    try {
      final deviceId = await DeviceId.get();
      await ApiClient.instance.post('/activity/heartbeat', body: {'device_id': deviceId});
    } catch (_) {
      // A missed heartbeat just means the backend will see the user as
      // offline a little sooner; nothing for the user to act on.
    } finally {
      _sending = false;
    }
  }
}
