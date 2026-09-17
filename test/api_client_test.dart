import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:quizs/services/api_client.dart';

/// An error response whose `errors` field isn't a JSON object (e.g. a List
/// or a String) must still surface as a clean ApiException, not a raw
/// TypeError that escapes every `on ApiException catch` handler in the app.
void main() {
  test('a non-map errors field does not throw a raw TypeError', () async {
    ApiClient.instance.debugClient = MockClient((request) async {
      return http.Response(
        jsonEncode({'success': false, 'message': 'Bad request', 'errors': ['first', 'second']}),
        422,
        headers: {'content-type': 'application/json'},
      );
    });

    await expectLater(
      () => ApiClient.instance.get('/whatever'),
      throwsA(isA<ApiException>().having((e) => e.message, 'message', 'Bad request')),
    );
  });

  test('a well-formed map errors field still surfaces its first message', () async {
    ApiClient.instance.debugClient = MockClient((request) async {
      return http.Response(
        jsonEncode({
          'success': false,
          'message': 'Validation failed',
          'errors': {'email': ['The email field is required.']},
        }),
        422,
        headers: {'content-type': 'application/json'},
      );
    });

    await expectLater(
      () => ApiClient.instance.get('/whatever'),
      throwsA(isA<ApiException>().having((e) => e.message, 'message', 'The email field is required.')),
    );
  });
}
