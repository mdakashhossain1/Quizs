import 'dart:math';

import 'package:shared_preferences/shared_preferences.dart';

/// A persistent, per-install random identifier used to tell one device's
/// activity sessions apart from another's for the same account — not a
/// hardware identifier, just a locally generated UUID cached in prefs.
class DeviceId {
  DeviceId._();

  static const String _prefsKey = 'device_id';
  static String? _cached;

  static Future<String> get() async {
    if (_cached != null) return _cached!;

    final prefs = await SharedPreferences.getInstance();
    var id = prefs.getString(_prefsKey);
    if (id == null || id.isEmpty) {
      id = _generateV4();
      await prefs.setString(_prefsKey, id);
    }
    _cached = id;
    return id;
  }

  static String _generateV4() {
    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    final hex = bytes.map((b) => b.toRadixString(16).padLeft(2, '0')).join();
    return '${hex.substring(0, 8)}-${hex.substring(8, 12)}-${hex.substring(12, 16)}-'
        '${hex.substring(16, 20)}-${hex.substring(20, 32)}';
  }
}
