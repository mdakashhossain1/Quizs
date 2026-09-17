import 'package:flutter_secure_storage_platform_interface/flutter_secure_storage_platform_interface.dart';

/// `flutter_secure_storage`'s real platform channel never resolves under
/// `flutter test` (no native implementation is registered), which leaves the
/// app's login/logout flow waiting forever. Registering this in-memory fake
/// as the platform implementation makes those calls behave like a real
/// (if non-persistent) secure store instead.
class FakeSecureStorage extends FlutterSecureStoragePlatform {
  final Map<String, String> _values = {};

  @override
  Future<void> write({
    required String key,
    required String value,
    required Map<String, String> options,
  }) async {
    _values[key] = value;
  }

  @override
  Future<String?> read({
    required String key,
    required Map<String, String> options,
  }) async {
    return _values[key];
  }

  @override
  Future<bool> containsKey({
    required String key,
    required Map<String, String> options,
  }) async {
    return _values.containsKey(key);
  }

  @override
  Future<void> delete({
    required String key,
    required Map<String, String> options,
  }) async {
    _values.remove(key);
  }

  @override
  Future<Map<String, String>> readAll({
    required Map<String, String> options,
  }) async {
    return Map.of(_values);
  }

  @override
  Future<void> deleteAll({
    required Map<String, String> options,
  }) async {
    _values.clear();
  }
}

void installFakeSecureStorage() {
  FlutterSecureStoragePlatform.instance = FakeSecureStorage();
}
