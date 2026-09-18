import 'dart:convert';
import 'dart:io';

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

  // Deployed on shared hosting via the root-level index.php/.htaccess
  // bridge in admin/ (see admin/.htaccess), so the API is reachable
  // directly at /api/... with no /admin/public prefix.
  static const String baseUrl = 'https://quizs.in/api';

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

  /// Multipart POST for file uploads (e.g. a profile picture) — a JSON body
  /// can't carry binary data, so this bypasses [post] entirely rather than
  /// base64-encoding the file into it.
  Future<Map<String, dynamic>> postMultipart(
    String path, {
    required String fileField,
    required File file,
  }) async {
    final response = await _send((client) async {
      final request = http.MultipartRequest('POST', Uri.parse('$baseUrl$path'))
        ..headers.addAll({
          'Accept': 'application/json',
          if (_token != null) 'Authorization': 'Bearer $_token',
        })
        ..files.add(await http.MultipartFile.fromPath(fileField, file.path));
      final streamed = await client.send(request);
      return http.Response.fromStream(streamed);
    });
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

    final rawErrors = data['errors'];
    final errors = rawErrors is Map ? rawErrors.cast<String, dynamic>() : null;
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
