import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:pubscale_offerwall_plugin/pubscale_offerwall_plugin.dart';

import 'auth_service.dart';

/// Wraps the PubScale OfferWall SDK for the Quizs application.
///
/// Handles initialization, event listening, and opening the offerwall.
/// Uses the logged-in user's email as the unique user identifier.
class PubscaleOfferwallService extends ChangeNotifier {
  PubscaleOfferwallService._();
  static final PubscaleOfferwallService instance =
      PubscaleOfferwallService._();

  /// PubScale App ID provided by the dashboard.
  static const String _appId = '52785494';

  final PubscaleOfferwallPlugin _plugin = PubscaleOfferwallPlugin();

  bool _isInitialized = false;
  bool _isInitializing = false;
  String? _lastError;
  StreamSubscription<dynamic>? _eventSubscription;

  bool get isInitialized => _isInitialized;
  bool get isInitializing => _isInitializing;
  String? get lastError => _lastError;

  /// Initializes the PubScale OfferWall SDK.
  ///
  /// Should be called early in the app lifecycle (e.g. from `main.dart`).
  /// Uses the current user's email as the unique user ID for attribution.
  Future<void> initialize() async {
    if (_isInitialized || _isInitializing) return;
    _isInitializing = true;
    _lastError = null;
    notifyListeners();

    try {
      // Listen for SDK events via the EventChannel stream
      _eventSubscription?.cancel();
      _eventSubscription = _plugin.offerwallEvents.listen(
        (event) {
          debugPrint('[PubScale] OfferWall event: $event');
          if (event is Map) {
            final type = (event['event'] ?? event['type'] ?? '').toString();
            switch (type) {
              case 'offerwall_init_success':
              case 'onInitSuccess':
              case 'init_success':
                _isInitialized = true;
                _isInitializing = false;
                _lastError = null;
                notifyListeners();
                break;
              case 'offerwall_init_failed':
              case 'onInitError':
              case 'init_error':
                _isInitialized = false;
                _isInitializing = false;
                _lastError = event['message']?.toString() ??
                    event['error']?.toString() ??
                    'Initialization failed';
                notifyListeners();
                break;
              case 'offerwall_showed':
                debugPrint('[PubScale] OfferWall showed');
                break;
              case 'offerwall_closed':
              case 'onOfferwallClose':
              case 'offerwall_close':
                debugPrint('[PubScale] OfferWall closed by user');
                break;
              case 'offerwall_reward':
              case 'onReward':
              case 'reward':
                debugPrint(
                    '[PubScale] Reward received: ${event['amount'] ?? event['reward']} ${event['currency'] ?? ''}');
                break;
              case 'offerwall_launch_failed':
                debugPrint('[PubScale] OfferWall launch failed: ${event['error']}');
                _lastError = event['error']?.toString() ?? 'Launch failed';
                notifyListeners();
                break;
              default:
                debugPrint('[PubScale] Unhandled event: $type');
            }
          } else if (event is String) {
            debugPrint('[PubScale] OfferWall string event: $event');
            if (event.contains('init_success') ||
                event.contains('offerwall_init_success') ||
                event.contains('onInitSuccess')) {
              _isInitialized = true;
              _isInitializing = false;
              _lastError = null;
              notifyListeners();
            } else if (event.contains('init_error') ||
                event.contains('offerwall_init_failed') ||
                event.contains('onInitError')) {
              _isInitialized = false;
              _isInitializing = false;
              _lastError = event;
              notifyListeners();
            }
          }
        },
        onError: (error) {
          debugPrint('[PubScale] OfferWall event stream error: $error');
          _isInitializing = false;
          _lastError = error.toString();
          notifyListeners();
        },
      );

      // Determine the unique user ID for attribution
      final auth = AuthService.instance;
      final uniqueUserId = auth.isLoggedIn && auth.userEmail.isNotEmpty
          ? auth.userEmail
          : 'anonymous_${DateTime.now().millisecondsSinceEpoch}';

      // Initialize the SDK (4 positional arguments: appId, uniqueUserId, sandbox, fullscreen)
      await _plugin.initializeOfferwall(
        _appId,
        uniqueUserId,
        false, // sandbox: false
        false, // fullscreen: false
      );
      _isInitialized = true;
      _isInitializing = false;
      notifyListeners();

      debugPrint(
          '[PubScale] OfferWall initialized with userId: $uniqueUserId');
    } catch (e) {
      debugPrint('[PubScale] OfferWall initialization exception: $e');
      _isInitializing = false;
      _lastError = e.toString();
      notifyListeners();
    }
  }

  /// Re-initializes the SDK with the current user's credentials.
  ///
  /// Call this after login/logout to update the user identifier.
  Future<void> reinitialize() async {
    _isInitialized = false;
    _isInitializing = false;
    _lastError = null;
    _eventSubscription?.cancel();
    _eventSubscription = null;
    await initialize();
  }

  /// Opens the PubScale OfferWall.
  ///
  /// The SDK must be initialized first. If not initialized, this will
  /// attempt to initialize and then launch.
  Future<void> openOfferwall() async {
    if (!_isInitialized) {
      if (!_isInitializing) {
        await initialize();
      }
      // Wait briefly if initialization call is in progress
      int retries = 0;
      while (_isInitializing && retries < 10) {
        await Future.delayed(const Duration(milliseconds: 200));
        retries++;
      }
    }

    try {
      await _plugin.launchOfferwall();
      debugPrint('[PubScale] OfferWall launched');
    } catch (e) {
      debugPrint('[PubScale] Error launching OfferWall: $e');
      rethrow;
    }
  }
}
