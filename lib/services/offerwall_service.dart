import 'pubscale_offerwall_service.dart';

/// Orchestrates offerwall access for the application.
///
/// Delegates to [PubscaleOfferwallService] for the native PubScale SDK
/// integration. The previous URL-launcher approach has been replaced.
class OfferwallService {
  OfferwallService();

  static final instance = OfferwallService();

  /// Opens the PubScale OfferWall.
  ///
  /// [shouldOpen] – optional guard callback; if it returns `false` the
  /// offerwall will not be shown (e.g. the widget is no longer mounted).
  Future<void> open({bool Function()? shouldOpen}) async {
    if (shouldOpen != null && !shouldOpen()) return;
    await PubscaleOfferwallService.instance.openOfferwall();
  }
}
