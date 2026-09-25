import 'package:url_launcher/url_launcher.dart';

import 'api_client.dart';

class OfferwallService {
  OfferwallService({Future<bool> Function(Uri)? launcher})
    : _launcher = launcher ?? _launch;

  static final instance = OfferwallService();
  final Future<bool> Function(Uri) _launcher;

  static Future<bool> _launch(Uri uri) => launchUrl(
    uri,
    mode: LaunchMode.inAppBrowserView,
    webOnlyWindowName: '_self',
  );

  Future<void> open({bool Function()? shouldOpen}) async {
    final data = await ApiClient.instance.post('/offerwall/launch');
    final rawUrl = data['url'];
    final uri = rawUrl is String ? Uri.tryParse(rawUrl) : null;
    if (uri == null ||
        uri.scheme != 'https' ||
        uri.host != 'rewards.unity.com' ||
        uri.userInfo.isNotEmpty ||
        uri.port != 443 ||
        !uri.path.startsWith('/owp/web/link/')) {
      throw ApiException(
        'Offerwall is currently unavailable.',
        statusCode: 503,
      );
    }
    if (shouldOpen != null && !shouldOpen()) return;
    if (!await _launcher(uri)) {
      throw ApiException('Could not open Offerwall. Please try again.');
    }
  }
}
