import 'dart:convert';
import 'dart:io' show Platform;

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.errors, this.body});

  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  /// The full decoded JSON error body, for callers that need to branch on
  /// domain-specific fields beyond `message`/`errors` (e.g. `requires_verification`).
  final Map<String, dynamic>? body;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient._();
  static final ApiClient instance = ApiClient._();

  // Laravel app lives in admin/ and is served by XAMPP directly from that
  // folder (admin/index.php + admin/.htaccess route into public/ internally,
  // the same layout used on shared hosting) — no /public in the URL.
  // These are plain JSON endpoints (no Livewire involved), so the nested
  // path is fine here — the admin panel's Livewire login needed its own
  // vhost (quizs-admin.local), but that's unrelated to this API and can't
  // be resolved from the Android emulator anyway.
  // 10.0.2.2 is how the Android emulator reaches the host machine's localhost;
  // a physical device needs the host's LAN IP instead.
  static String get baseUrl {
    if (!kIsWeb && Platform.isAndroid) {
      return 'http://10.0.2.2/Quizs/admin/api';
    }
    return 'http://localhost/Quizs/admin/api';
  }

  String? _token;
  http.Client _client = http.Client();

  void setToken(String? token) => _token = token;

  /// Overrides the underlying HTTP client — used by widget tests to stub
  /// server responses via `package:http/testing.dart`'s `MockClient`.
  @visibleForTesting
  set debugClient(http.Client client) => _client = client;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Future<Map<String, dynamic>> get(String path) async {
    final response = await _send((client) => client.get(
          Uri.parse('$baseUrl$path'),
          headers: _headers,
        ));
    return _decode(response);
  }

  Future<Map<String, dynamic>> post(String path, {Map<String, dynamic>? body}) async {
    final response = await _send((client) => client.post(
          Uri.parse('$baseUrl$path'),
          headers: _headers,
          body: jsonEncode(body ?? {}),
        ));
    return _decode(response);
  }

  Future<http.Response> _send(
    Future<http.Response> Function(http.Client client) request,
  ) async {
    try {
      return await request(_client).timeout(const Duration(seconds: 15));
    } on ApiException {
      rethrow;
    } catch (_) {
      throw ApiException('Could not reach the server. Please check your connection.');
    }
  }

  Map<String, dynamic> _decode(http.Response response) {
    Map<String, dynamic> data;
    try {
      data = response.body.isEmpty
          ? <String, dynamic>{}
          : jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      data = <String, dynamic>{};
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return data;
    }

    final errors = data['errors'] as Map<String, dynamic>?;
    var message = data['message'] as String? ?? 'Something went wrong.';
    if (errors != null && errors.isNotEmpty) {
      final firstError = errors.values.first;
      if (firstError is List && firstError.isNotEmpty) {
        message = firstError.first.toString();
      }
    }
    throw ApiException(message, statusCode: response.statusCode, errors: errors, body: data);
  }
}
