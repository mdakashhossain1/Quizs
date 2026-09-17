import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import 'design_widgets.dart';

class QuizBottomNav extends StatelessWidget {
  const QuizBottomNav({
    super.key,
    this.initialIndex = 0,
    this.onTabSelected,
  });

  final int initialIndex;
  final ValueChanged<int>? onTabSelected;

  List<_NavItem> _getItems() => [
    _NavItem(
      label: AppStrings.t('home'),
      actionLabel: 'Home',
      type: _NavIconType.home,
      pillWidth: AppLanguage.instance.isHindi ? 114 : 106,
    ),
    _NavItem(
      label: AppStrings.t('category'),
      actionLabel: 'Category',
      type: _NavIconType.dashboard,
      pillWidth: AppLanguage.instance.isHindi ? 122 : 124,
    ),
    _NavItem(
      label: AppStrings.t('profile'),
      actionLabel: 'Profile',
      type: _NavIconType.user,
      pillWidth: AppLanguage.instance.isHindi ? 122 : 106,
    ),
    _NavItem(
      label: AppStrings.t('attendance'),
      actionLabel: 'Attendance',
      type: _NavIconType.attendance,
      pillWidth: AppLanguage.instance.isHindi ? 122 : 132,
    ),
  ];

  void _onItemTapped(BuildContext context, int index) {
    if (index == initialIndex) return;
    if (onTabSelected != null) {
      onTabSelected!(index);
    } else {
      switch (index) {
        case 0:
          Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
          break;
        case 1:
          Navigator.pushNamed(context, '/categories');
          break;
        case 2:
          Navigator.pushNamed(context, '/profile');
          break;
        case 3:
          Navigator.pushNamed(context, '/attendance');
          break;
      }
    }
  }

  /// The first/last pill hug the track's edges (12px in); anything in
  /// between centers within its own even share of the remaining width. For
  /// the original 3-tab layout this reduces to exactly the same numbers as
  /// before — the formula just also works for a 4th tab.
  double _getPillLeft(int index, List<_NavItem> items) {
    const totalWidth = 384.0;
    const edgeMargin = 12.0;
    if (index == 0) return edgeMargin;
    if (index == items.length - 1) {
      return totalWidth - items[index].pillWidth - edgeMargin;
    }
    final regionWidth = totalWidth / items.length;
    return regionWidth * index + (regionWidth - items[index].pillWidth) / 2;
  }

  @override
  Widget build(BuildContext context) {
    final items = _getItems();
    const totalWidth = 384.0;
    const totalHeight = 58.0;
    const pillHeight = 39.0;
    final currentIndex = initialIndex.clamp(0, items.length - 1);
    final pillLeft = _getPillLeft(currentIndex, items);
    final currentPillWidth = items[currentIndex].pillWidth;


    return Container(
      width: totalWidth,
      height: totalHeight,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(29),
        border: Border.all(color: const Color(0x0F000000), width: 0.3),
        boxShadow: const [
          BoxShadow(
            color: Color(0x40000000),
            offset: Offset(1, 2),
            blurRadius: 4,
          ),
        ],
      ),
      child: Stack(
        alignment: Alignment.centerLeft,
        children: [
          // Active purple pill indicator
          Positioned(
            left: pillLeft,
            top: (totalHeight - pillHeight) / 2,
            width: currentPillWidth,
            height: pillHeight,
            child: Container(
              decoration: BoxDecoration(
                color: const Color(0xFF6703BF),
                borderRadius: BorderRadius.circular(54),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x336703BF),
                    offset: Offset(0, 3),
                    blurRadius: 6,
                  ),
                ],
              ),
            ),
          ),
          // Interactive tab items
          Row(
            children: List.generate(items.length, (index) {
              final item = items[index];
              final isActive = currentIndex == index;

              return Expanded(
                child: DesignAction(
                  label: item.actionLabel,
                  onTap: () => _onItemTapped(context, index),
                  child: Center(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        _buildNavIcon(
                          item.type,
                          isActive ? Colors.white : const Color(0x996900C5),
                        ),
                        if (isActive) ...[
                          const SizedBox(width: 5),
                          Flexible(
                            child: Text(
                              item.label,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontFamily: 'Poppins',
                                fontSize: AppLanguage.instance.isHindi
                                    ? 12
                                    : 14,
                                fontWeight: FontWeight.w600,
                                color: Colors.white,
                                letterSpacing: 0,
                                height: 1.2,
                              ),
                            ),
                          ),
                        ],

                      ],
                    ),
                  ),
                ),
              );
            }),
          ),
        ],
      ),
    );
  }


  Widget _buildNavIcon(_NavIconType type, Color color) {
    switch (type) {
      case _NavIconType.home:
        return _HomeVector(color: color);
      case _NavIconType.dashboard:
        return _DashboardVector(color: color);
      case _NavIconType.user:
        return _UserVector(color: color);
      case _NavIconType.attendance:
        return _AttendanceVector(color: color);
    }
  }
}

enum _NavIconType { home, dashboard, user, attendance }

class _NavItem {
  const _NavItem({
    required this.label,
    required this.actionLabel,
    required this.type,
    required this.pillWidth,
  });

  final String label;
  final String actionLabel;
  final _NavIconType type;
  final double pillWidth;
}

class _HomeVector extends StatelessWidget {
  const _HomeVector({required this.color});
  final Color color;
  static const double size = 20;

  @override
  Widget build(BuildContext context) => CustomPaint(
        size: const Size(size, size),
        painter: _HomePainter(color),
      );
}

class _HomePainter extends CustomPainter {
  const _HomePainter(this.color);
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 20.0;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;

    final path = Path()
      ..moveTo(10 * s, 1.5 * s)
      ..lineTo(1.5 * s, 8.5 * s)
      ..lineTo(3.5 * s, 8.5 * s)
      ..lineTo(3.5 * s, 18.5 * s)
      ..lineTo(8 * s, 18.5 * s)
      ..lineTo(8 * s, 12.5 * s)
      ..arcToPoint(
        Offset(12 * s, 12.5 * s),
        radius: Radius.circular(2 * s),
      )
      ..lineTo(12 * s, 18.5 * s)
      ..lineTo(16.5 * s, 18.5 * s)
      ..lineTo(16.5 * s, 8.5 * s)
      ..lineTo(18.5 * s, 8.5 * s)
      ..close();

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _HomePainter oldDelegate) =>
      oldDelegate.color != color;
}

class _DashboardVector extends StatelessWidget {
  const _DashboardVector({required this.color});
  final Color color;
  static const double size = 22;

  @override
  Widget build(BuildContext context) => CustomPaint(
        size: const Size(size, size),
        painter: _DashboardPainter(color),
      );
}

class _DashboardPainter extends CustomPainter {
  const _DashboardPainter(this.color);
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 22.0;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.0 * s;

    final radius = Radius.circular(2.5 * s);
    const cellSize = 8.0;
    const gap = 3.5;

    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(1.5 * s, 1.5 * s, cellSize * s, cellSize * s),
        radius,
      ),
      paint,
    );
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(
          (1.5 + cellSize + gap) * s,
          1.5 * s,
          cellSize * s,
          cellSize * s,
        ),
        radius,
      ),
      paint,
    );
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(
          1.5 * s,
          (1.5 + cellSize + gap) * s,
          cellSize * s,
          cellSize * s,
        ),
        radius,
      ),
      paint,
    );
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(
          (1.5 + cellSize + gap) * s,
          (1.5 + cellSize + gap) * s,
          cellSize * s,
          cellSize * s,
        ),
        radius,
      ),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant _DashboardPainter oldDelegate) =>
      oldDelegate.color != color;
}

class _UserVector extends StatelessWidget {
  const _UserVector({required this.color});
  final Color color;
  static const double size = 22;

  @override
  Widget build(BuildContext context) => CustomPaint(
        size: const Size(size, size),
        painter: _UserPainter(color),
      );
}

class _UserPainter extends CustomPainter {
  const _UserPainter(this.color);
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 22.0;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.0 * s
      ..strokeCap = StrokeCap.round;

    canvas.drawCircle(Offset(11 * s, 6.5 * s), 4.5 * s, paint);

    final path = Path()
      ..moveTo(3.5 * s, 20 * s)
      ..cubicTo(
        3.5 * s,
        15 * s,
        7.5 * s,
        13.5 * s,
        11 * s,
        13.5 * s,
      )
      ..cubicTo(
        14.5 * s,
        13.5 * s,
        18.5 * s,
        15 * s,
        18.5 * s,
        20 * s,
      );

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _UserPainter oldDelegate) =>
      oldDelegate.color != color;
}

class _AttendanceVector extends StatelessWidget {
  const _AttendanceVector({required this.color});
  final Color color;
  static const double size = 22;

  @override
  Widget build(BuildContext context) => CustomPaint(
        size: const Size(size, size),
        painter: _AttendancePainter(color),
      );
}

class _AttendancePainter extends CustomPainter {
  const _AttendancePainter(this.color);
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 22.0;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.8 * s
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(2.5 * s, 4 * s, 17 * s, 15.5 * s),
        Radius.circular(2.5 * s),
      ),
      paint,
    );
    canvas.drawLine(Offset(2.5 * s, 9 * s), Offset(19.5 * s, 9 * s), paint);
    canvas.drawLine(Offset(7 * s, 2 * s), Offset(7 * s, 6 * s), paint);
    canvas.drawLine(Offset(15 * s, 2 * s), Offset(15 * s, 6 * s), paint);

    final check = Path()
      ..moveTo(7.5 * s, 14 * s)
      ..lineTo(10 * s, 16.5 * s)
      ..lineTo(15 * s, 11.5 * s);
    canvas.drawPath(check, paint);
  }

  @override
  bool shouldRepaint(covariant _AttendancePainter oldDelegate) =>
      oldDelegate.color != color;
}
