import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  int _selectedFilter = 0; // 0: All, 1: Challenges, 2: Rewards

  final List<_NotificationItem> _notifications = [
    _NotificationItem(
      id: '1',
      titleKey: 'notif_sprint_title',
      messageKey: 'notif_sprint_desc',
      timeKey: 'time_10m',
      type: _NotificationType.challenge,
      isUnread: true,
      route: '/question',
    ),
    _NotificationItem(
      id: '2',
      titleKey: 'notif_rank_title',
      messageKey: 'notif_rank_desc',
      timeKey: 'time_2h',
      type: _NotificationType.rank,
      isUnread: true,
      route: '/dashboard',
    ),
    _NotificationItem(
      id: '3',
      titleKey: 'notif_quiz_title',
      messageKey: 'notif_quiz_desc',
      timeKey: 'time_1d',
      type: _NotificationType.quiz,
      isUnread: false,
      route: '/mathematics',
    ),
    _NotificationItem(
      id: '4',
      titleKey: 'notif_reward_title',
      messageKey: 'notif_reward_desc',
      timeKey: 'time_3d',
      type: _NotificationType.reward,
      isUnread: false,
      route: '/profile',
    ),
  ];

  void _markAllAsRead() {
    setState(() {
      for (final n in _notifications) {
        n.isUnread = false;
      }
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(AppStrings.t('all_notifs_read')),
        duration: const Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filteredNotifications = switch (_selectedFilter) {
      1 => _notifications
          .where(
            (n) =>
                n.type == _NotificationType.challenge ||
                n.type == _NotificationType.quiz,
          )
          .toList(),
      2 => _notifications
          .where(
            (n) =>
                n.type == _NotificationType.rank ||
                n.type == _NotificationType.reward,
          )
          .toList(),
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
                  count: 2,
                  isSelected: _selectedFilter == 1,
                  onTap: () => setState(() => _selectedFilter = 1),
                ),
                const SizedBox(width: 8),
                _FilterChip(
                  label: AppStrings.t('rewards_filter'),
                  count: 2,
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

        // Notifications List Cards
        for (var i = 0; i < filteredNotifications.length; i++) ...[
          at(
            24,
            254 + i * 106,
            364,
            96,
            AnimatedSection(
              delay: Duration(milliseconds: 70 + i * 50),
              child: _NotificationCard(
                item: filteredNotifications[i],
                onTap: () {
                  setState(() => filteredNotifications[i].isUnread = false);
                  if (filteredNotifications[i].route != null) {
                    Navigator.pushNamed(
                      context,
                      filteredNotifications[i].route!,
                    );
                  }
                },
              ),
            ),
          ),
        ],
      ],
    );
  }
}

enum _NotificationType { challenge, rank, quiz, reward }

class _NotificationItem {
  _NotificationItem({
    required this.id,
    required this.titleKey,
    required this.messageKey,
    required this.timeKey,
    required this.type,
    required this.isUnread,
    this.route,
  });

  final String id;
  final String titleKey;
  final String messageKey;
  final String timeKey;
  final _NotificationType type;
  bool isUnread;
  final String? route;

  String get title => AppStrings.t(titleKey);
  String get message => AppStrings.t(messageKey);
  String get time => AppStrings.t(timeKey);
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

  final _NotificationItem item;
  final VoidCallback onTap;

  Color get _accentColor => switch (item.type) {
    _NotificationType.challenge => const Color(0xFF6703BF),
    _NotificationType.rank => const Color(0xFFFF9800),
    _NotificationType.quiz => const Color(0xFF10BA65),
    _NotificationType.reward => const Color(0xFF8E24AA),
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
          color: item.isUnread ? const Color(0xFFFAF7FE) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: item.isUnread
                ? const Color(0x306703BF)
                : const Color(0x14000000),
            width: item.isUnread ? 1.2 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: item.isUnread
                  ? const Color(0x0C6703BF)
                  : const Color(0x06000000),
              offset: const Offset(0, 3),
              blurRadius: 8,
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Icon badge
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
                  painter: _NotificationIconPainter(item.type, _accentColor),
                ),
              ),
            ),
            const SizedBox(width: 12),

            // Text contents
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
                            fontWeight: item.isUnread
                                ? FontWeight.w700
                                : FontWeight.w600,
                            color: const Color(0xFF1E1E1E),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        item.time,
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
                    item.message,
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

            // Unread dot
            if (item.isUnread) ...[
              const SizedBox(width: 8),
              Container(
                width: 8,
                height: 8,
                margin: const EdgeInsets.only(top: 4),
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  color: QuizColors.purple,
                ),
              ),
            ],
          ],
        ),
      ),
    ),
  );
}

class _NotificationIconPainter extends CustomPainter {
  const _NotificationIconPainter(this.type, this.color);
  final _NotificationType type;
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

    switch (type) {
      case _NotificationType.challenge:
        // Flame / lightning bolt icon
        final path = Path()
          ..moveTo(w * 0.55, h * 0.1)
          ..lineTo(w * 0.25, h * 0.55)
          ..lineTo(w * 0.5, h * 0.55)
          ..lineTo(w * 0.45, h * 0.9)
          ..lineTo(w * 0.75, h * 0.45)
          ..lineTo(w * 0.5, h * 0.45)
          ..close();
        canvas.drawPath(path, paint..style = PaintingStyle.fill);
        break;

      case _NotificationType.rank:
        // Trophy icon
        final cup = Path()
          ..moveTo(w * 0.25, h * 0.2)
          ..lineTo(w * 0.75, h * 0.2)
          ..lineTo(w * 0.7, h * 0.55)
          ..arcToPoint(
            Offset(w * 0.3, h * 0.55),
            radius: Radius.circular(w * 0.25),
          )
          ..close();
        canvas.drawPath(cup, paint);
        canvas.drawLine(Offset(w * 0.5, h * 0.65), Offset(w * 0.5, h * 0.8), paint);
        canvas.drawLine(Offset(w * 0.3, h * 0.8), Offset(w * 0.7, h * 0.8), paint);
        break;

      case _NotificationType.quiz:
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
        break;

      case _NotificationType.reward:
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
        break;
    }
  }

  @override
  bool shouldRepaint(covariant _NotificationIconPainter oldDelegate) =>
      oldDelegate.type != type || oldDelegate.color != color;
}
