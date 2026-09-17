import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/notification_navigation.dart';
import '../services/notifications_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';
import '../widgets/shimmer_loading.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  int _selectedFilter = 0; // 0: All, 1: Quizs, 2: Rewards
  List<AppNotification> _notifications = const [];
  bool _loading = true;
  bool _loadFailed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _loadFailed = false;
    });
    try {
      final notifications = await NotificationsService.instance.fetch();
      if (!mounted) return;
      setState(() {
        _notifications = notifications;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loadFailed = true;
        _loading = false;
      });
    }
  }

  void _showSnack(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), duration: const Duration(seconds: 2), behavior: SnackBarBehavior.floating),
    );
  }

  Future<void> _markAllAsRead() async {
    final previous = _notifications;
    final hadUnread = previous.any((n) => !n.isRead);
    if (!hadUnread) return;

    setState(() {
      _notifications = [for (final n in previous) n.copyWith(isRead: true)];
    });

    try {
      await NotificationsService.instance.markAllRead();
      _showSnack(AppStrings.t('all_notifs_read'));
    } catch (_) {
      // Roll back: the backend never confirmed the change, so the UI must
      // not keep claiming everything is read.
      if (mounted) setState(() => _notifications = previous);
      _showSnack('Could not mark notifications as read. Please try again.');
    }
  }

  Future<void> _openNotification(AppNotification item) async {
    if (!item.isRead) {
      final previous = _notifications;
      setState(() {
        _notifications = [for (final n in previous) n.id == item.id ? n.copyWith(isRead: true) : n];
      });

      try {
        await NotificationsService.instance.markRead(item.id);
      } catch (_) {
        if (mounted) setState(() => _notifications = previous);
      }
    }
    if (mounted) {
      await openNotificationDestination(
        context,
        destinationType: item.destinationType,
        destinationId: item.destinationId,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final filteredNotifications = switch (_selectedFilter) {
      1 => _notifications.where((n) => n.destinationType == 'quiz_details').toList(),
      2 => _notifications.where((n) => n.destinationType == 'achievement').toList(),
      _ => _notifications,
    };

    return DesignCanvas(
      adTop: 720,
      adBefore: 841,
      color: const Color(0xFFFCFAFE),
      topColor: QuizColors.darkPurple,
      bottomNav: QuizBottomNav(
        initialIndex: 0,
        onTabSelected: (index) {
          if (index == 0) {
            Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
          } else if (index == 1) {
            Navigator.pushNamed(context, '/dashboard');
          } else if (index == 2) {
            Navigator.pushNamed(context, '/attendance');
          } else if (index == 3) {
            Navigator.pushNamed(context, '/profile');
          }
        },
      ),
      children: [
        at(0, -139, 412, 467, const PurpleHeader()),
        backButton(context),
        label(
          AppStrings.t('notifications'),
          0,
          126,
          28,
          width: 412,
          align: TextAlign.center,
          color: Colors.white,
          weight: FontWeight.w700,
        ),
        panel(0, 187, 412, 745, Colors.white, radius: 31),

        // Controls bar: Filter Chips & Mark All Read
        at(
          24,
          208,
          364,
          34,
          AnimatedSection(
            delay: const Duration(milliseconds: 40),
            child: Row(
              children: [
                _FilterChip(
                  label: AppStrings.t('all_filter'),
                  count: _notifications.length,
                  isSelected: _selectedFilter == 0,
                  onTap: () => setState(() => _selectedFilter = 0),
                ),
                const SizedBox(width: 8),
                _FilterChip(
                  label: AppStrings.t('quizs_filter'),
                  count: _notifications.where((n) => n.destinationType == 'quiz_details').length,
                  isSelected: _selectedFilter == 1,
                  onTap: () => setState(() => _selectedFilter = 1),
                ),
                const SizedBox(width: 8),
                _FilterChip(
                  label: AppStrings.t('rewards_filter'),
                  count: _notifications.where((n) => n.destinationType == 'achievement').length,
                  isSelected: _selectedFilter == 2,
                  onTap: () => setState(() => _selectedFilter = 2),
                ),
                const Spacer(),
                GestureDetector(
                  onTap: _markAllAsRead,
                  child: Text(
                    AppStrings.t('mark_all_read'),
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: QuizColors.purple,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),

        if (_loading)
          at(
            0,
            260,
            412,
            420,
            Column(
              children: List.generate(
                6,
                (i) => AnimatedSection(
                  delay: Duration(milliseconds: i * 60),
                  child: const NotificationCardShimmer(),
                ),
              ),
            ),
          )
        else if (_loadFailed)
          at(
            24,
            290,
            364,
            60,
            Center(
              child: Text(
                'Could not load notifications. Pull to refresh.',
                textAlign: TextAlign.center,
                style: const TextStyle(fontFamily: 'Poppins', fontSize: 13, color: Color(0xFF9E9E9E)),
              ),
            ),
          )
        else if (filteredNotifications.isEmpty)
          at(
            24,
            270,
            364,
            220,
            AnimatedSection(
              delay: const Duration(milliseconds: 100),
              child: NoDataView(
                message: AppStrings.t('no_notifications'),
                subMessage: 'You are all caught up!',
                imageSize: 105,
              ),
            ),
          )
        else
          for (var i = 0; i < filteredNotifications.length; i++)
            at(
              24,
              254 + i * 106,
              364,
              96,
              AnimatedSection(
                delay: Duration(milliseconds: 70 + i * 50),
                child: _NotificationCard(
                  item: filteredNotifications[i],
                  onTap: () => _openNotification(filteredNotifications[i]),
                ),
              ),
            ),
      ],
    );
  }
}

/// Compact "2h ago" / "3d ago" style relative time for a notification's
/// server timestamp — no fixed demo strings, always derived from the real
/// `created_at` the backend returned.
String _relativeTime(DateTime time) {
  final diff = DateTime.now().difference(time);
  if (diff.inMinutes < 1) return 'now';
  if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
  if (diff.inHours < 24) return '${diff.inHours}h ago';
  if (diff.inDays < 30) return '${diff.inDays}d ago';
  return '${time.day}/${time.month}/${time.year}';
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.count,
    required this.isSelected,
    required this.onTap,
  });

  final String label;
  final int count;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: AnimatedContainer(
      duration: const Duration(milliseconds: 180),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: isSelected ? QuizColors.purple : const Color(0xFFF6F0FD),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Text(
        '$label ($count)',
        style: TextStyle(
          fontFamily: 'Poppins',
          fontSize: 12,
          fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
          color: isSelected ? Colors.white : QuizColors.purple,
        ),
      ),
    ),
  );
}

class _NotificationCard extends StatelessWidget {
  const _NotificationCard({required this.item, required this.onTap});

  final AppNotification item;
  final VoidCallback onTap;

  Color get _accentColor => switch (item.destinationType) {
    'quiz_details' => const Color(0xFF10BA65),
    'achievement' => const Color(0xFF8E24AA),
    'attendance' => const Color(0xFFFF9800),
    _ => const Color(0xFF6703BF),
  };

  @override
  Widget build(BuildContext context) => Material(
    color: Colors.transparent,
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: item.isRead ? Colors.white : const Color(0xFFFAF7FE),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: item.isRead ? const Color(0x14000000) : const Color(0x306703BF),
            width: item.isRead ? 1 : 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: item.isRead ? const Color(0x06000000) : const Color(0x0C6703BF),
              offset: const Offset(0, 3),
              blurRadius: 8,
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: _accentColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Center(
                child: CustomPaint(
                  size: const Size(20, 20),
                  painter: _NotificationIconPainter(item.destinationType, _accentColor),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.title,
                          style: TextStyle(
                            fontFamily: 'Poppins',
                            fontSize: 13,
                            fontWeight: item.isRead ? FontWeight.w600 : FontWeight.w700,
                            color: const Color(0xFF1E1E1E),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        _relativeTime(item.createdAt),
                        style: const TextStyle(
                          fontFamily: 'Poppins',
                          fontSize: 10,
                          fontWeight: FontWeight.w400,
                          color: Color(0xFF8E8E93),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(
                    item.body,
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 11,
                      fontWeight: FontWeight.w400,
                      color: Color(0xFF555555),
                      height: 1.3,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
            if (!item.isRead) ...[
              const SizedBox(width: 8),
              Container(
                width: 8,
                height: 8,
                margin: const EdgeInsets.only(top: 4),
                decoration: const BoxDecoration(shape: BoxShape.circle, color: QuizColors.purple),
              ),
            ],
          ],
        ),
      ),
    ),
  );
}

class _NotificationIconPainter extends CustomPainter {
  const _NotificationIconPainter(this.destinationType, this.color);
  final String destinationType;
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.6
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    final w = size.width;
    final h = size.height;

    switch (destinationType) {
      case 'quiz_details':
        // Question / book icon
        final book = Path()
          ..moveTo(w * 0.2, h * 0.25)
          ..lineTo(w * 0.5, h * 0.35)
          ..lineTo(w * 0.8, h * 0.25)
          ..lineTo(w * 0.8, h * 0.75)
          ..lineTo(w * 0.5, h * 0.85)
          ..lineTo(w * 0.2, h * 0.75)
          ..close();
        canvas.drawPath(book, paint);
        canvas.drawLine(Offset(w * 0.5, h * 0.35), Offset(w * 0.5, h * 0.85), paint);

      case 'achievement':
        // Star badge icon
        final star = Path()
          ..moveTo(w * 0.5, h * 0.15)
          ..lineTo(w * 0.62, h * 0.38)
          ..lineTo(w * 0.85, h * 0.42)
          ..lineTo(w * 0.68, h * 0.58)
          ..lineTo(w * 0.72, h * 0.82)
          ..lineTo(w * 0.5, h * 0.7)
          ..lineTo(w * 0.28, h * 0.82)
          ..lineTo(w * 0.32, h * 0.58)
          ..lineTo(w * 0.15, h * 0.42)
          ..lineTo(w * 0.38, h * 0.38)
          ..close();
        canvas.drawPath(star, paint..style = PaintingStyle.fill);

      case 'attendance':
        // Calendar/check icon
        canvas.drawRRect(
          RRect.fromRectAndRadius(Rect.fromLTWH(w * 0.15, h * 0.22, w * 0.7, h * 0.65), Radius.circular(w * 0.06)),
          paint,
        );
        canvas.drawLine(Offset(w * 0.3, h * 0.1), Offset(w * 0.3, h * 0.3), paint);
        canvas.drawLine(Offset(w * 0.7, h * 0.1), Offset(w * 0.7, h * 0.3), paint);

      default:
        // Generic bell icon for target/profile/general notices
        final bell = Path()
          ..moveTo(w * 0.3, h * 0.75)
          ..lineTo(w * 0.7, h * 0.75)
          ..cubicTo(w * 0.7, h * 0.55, w * 0.65, h * 0.5, w * 0.65, h * 0.35)
          ..cubicTo(w * 0.65, h * 0.2, w * 0.55, h * 0.12, w * 0.5, h * 0.12)
          ..cubicTo(w * 0.45, h * 0.12, w * 0.35, h * 0.2, w * 0.35, h * 0.35)
          ..cubicTo(w * 0.35, h * 0.5, w * 0.3, h * 0.55, w * 0.3, h * 0.75)
          ..close();
        canvas.drawPath(bell, paint);
        canvas.drawLine(Offset(w * 0.42, h * 0.82), Offset(w * 0.58, h * 0.82), paint);
    }
  }

  @override
  bool shouldRepaint(covariant _NotificationIconPainter oldDelegate) =>
      oldDelegate.destinationType != destinationType || oldDelegate.color != color;
}
