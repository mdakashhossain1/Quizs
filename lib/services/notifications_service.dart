import 'api_client.dart';

/// One server-driven push notification, as delivered through the in-app
/// inbox (push_notification_prd.md) — the same record the admin composed
/// and that triggered the device push, never fabricated client-side.
class AppNotification {
  const AppNotification({
    required this.id,
    required this.title,
    required this.body,
    this.imageUrl,
    required this.destinationType,
    this.destinationId,
    required this.createdAt,
    required this.isRead,
  });

  /// The recipient-row id — used to mark this specific notification read.
  final int id;
  final String title;
  final String body;
  final String? imageUrl;
  final String destinationType;
  final int? destinationId;
  final DateTime createdAt;
  final bool isRead;

  factory AppNotification.fromJson(Map<String, dynamic> json) => AppNotification(
        id: (json['id'] as num).toInt(),
        title: json['title'] as String? ?? '',
        body: json['body'] as String? ?? '',
        imageUrl: json['image_url'] as String?,
        destinationType: json['destination_type'] as String? ?? 'none',
        destinationId: (json['destination_id'] as num?)?.toInt(),
        createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
        isRead: json['is_read'] as bool? ?? false,
      );

  AppNotification copyWith({bool? isRead}) => AppNotification(
        id: id,
        title: title,
        body: body,
        imageUrl: imageUrl,
        destinationType: destinationType,
        destinationId: destinationId,
        createdAt: createdAt,
        isRead: isRead ?? this.isRead,
      );
}

class NotificationsService {
  NotificationsService._();
  static final NotificationsService instance = NotificationsService._();

  /// Throws [ApiException] on failure — callers should fall back to an
  /// empty list rather than show stale/fabricated notifications.
  Future<List<AppNotification>> fetch() async {
    final data = await ApiClient.instance.get('/notifications');
    final list = (data['notifications'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
    return list.map(AppNotification.fromJson).toList();
  }

  Future<void> markRead(int recipientId) async {
    await ApiClient.instance.post('/notifications/$recipientId/read');
  }

  Future<void> markAllRead() async {
    await ApiClient.instance.post('/notifications/read-all');
  }
}
