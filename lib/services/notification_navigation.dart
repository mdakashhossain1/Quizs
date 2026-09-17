import 'package:flutter/material.dart';

import 'quiz_api_service.dart';

/// Resolves a server-driven notification's `destination_type`/`destination_id`
/// (push_notification_prd.md §10) into an in-app navigation, shared between
/// the in-app notification list and a tapped system push — one place decides
/// what each destination means, so the two can never disagree.
Future<void> openNotificationDestination(
  BuildContext context, {
  required String destinationType,
  int? destinationId,
}) async {
  switch (destinationType) {
    case 'attendance':
      Navigator.of(context).pushNamed('/attendance');
    case 'achievement':
      Navigator.of(context).pushNamed('/achievements');
    case 'target_progress':
    case 'profile':
      Navigator.of(context).pushNamed('/profile');
    case 'quiz_details':
      if (destinationId == null) {
        Navigator.of(context).pushNamed('/categories');
        return;
      }
      try {
        final topic = await QuizApiService.instance.fetchQuizTopic(destinationId);
        if (context.mounted) {
          Navigator.of(context).pushNamed('/question', arguments: topic);
        }
      } catch (_) {
        // Roadmap failure-handling: an invalid/unavailable destination opens
        // a safe default instead of crashing or doing nothing.
        if (context.mounted) Navigator.of(context).pushNamed('/categories');
      }
    default:
      // 'none' (a general announcement) — nothing further to open beyond
      // the notification itself, already visible in the inbox.
      break;
  }
}
