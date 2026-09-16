import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/auth_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) => DesignCanvas(
    adTop: 820,
    adBefore: 841,
    color: const Color(0xFFFCFAFE),
    topColor: QuizColors.darkPurple,
    bottomNav: QuizBottomNav(
      initialIndex: 1,
      onTabSelected: (index) {
        if (index == 0) {
          Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
        } else if (index == 2) {
          Navigator.pushNamed(context, '/profile');
        }
      },
    ),
    children: [
      at(0, -139, 412, 467, const PurpleHeader()),
      backButton(context),
      label(
        AppStrings.t('dashboard'),
        0,
        126,
        28,
        width: 412,
        align: TextAlign.center,
        color: Colors.white,
        weight: FontWeight.w700,
      ),
      panel(0, 187, 412, 745, Colors.white, radius: 31),

      // User Overview Card
      at(
        24,
        210,
        364,
        84,
        AnimatedSection(
          delay: const Duration(milliseconds: 50),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF6703BF), Color(0xFF8E24AA)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: const [
              BoxShadow(
                color: Color(0x336703BF),
                offset: Offset(0, 4),
                blurRadius: 10,
              ),
            ],
          ),
          child: Row(
            children: [
              ClipOval(
                child: SizedBox(
                  width: 52,
                  height: 52,
                  child: const FigmaAsset('75-2_imgProfile6.png'),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      AuthService.instance.userName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        height: 1.15,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      AppStrings.t('dashboard_user_rank'),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: Color(0xCCFFFFFF),
                        height: 1.15,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    ),

      // Stats 2x2 Grid
      at(
        24,
        308,
        364,
        156,
        AnimatedSection(
          delay: const Duration(milliseconds: 100),
          child: Row(
          children: [
            Expanded(
              child: Column(
                children: [
                  _StatTile(
                    label: AppStrings.t('quizzes_played'),
                    value: '132',
                    badge: _StatBadge.quiz,
                    color: const Color(0xFF53009C),
                  ),
                  const SizedBox(height: 10),
                  _StatTile(
                    label: AppStrings.t('current_streak'),
                    value: AppStrings.t('days_streak'),
                    badge: _StatBadge.streak,
                    color: const Color(0xFFFF6D00),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                children: [
                  _StatTile(
                    label: AppStrings.t('win_accuracy'),
                    value: '87%',
                    badge: _StatBadge.accuracy,
                    color: const Color(0xFF10BA65),
                  ),
                  const SizedBox(height: 10),
                  _StatTile(
                    label: AppStrings.t('total_score'),
                    value: '1,450 pts',
                    badge: _StatBadge.score,
                    color: const Color(0xFF2979FF),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      ),

      // Ongoing Challenge Section
      at(
        24,
        480,
        364,
        138,
        AnimatedSection(
          delay: const Duration(milliseconds: 150),
          child: Container(
            padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFFF6F0FD),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0x246703BF)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      AppStrings.t('daily_sprint_title'),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: QuizColors.purple,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 3,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFF6703BF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      AppStrings.t('sprint_progress'),
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: const LinearProgressIndicator(
                  value: 0.6,
                  minHeight: 8,
                  backgroundColor: Color(0x336703BF),
                  valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF10BA65)),
                ),
              ),
              const Spacer(),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      AppStrings.t('next_topic'),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF555555),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: () => Navigator.pushNamed(context, '/question'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF10BA65),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 6,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      elevation: 0,
                    ),
                    child: Text(
                      AppStrings.t('continue_btn'),
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    ),

      // Recent Scores Header
      label(
        AppStrings.t('recent_performances'),
        26,
        632,
        16,
        weight: FontWeight.w700,
        color: QuizColors.purple,
      ),

      // Recent Quizzes
      at(
        24,
        662,
        364,
        146,
        AnimatedSection(
          delay: const Duration(milliseconds: 200),
          child: Column(
            children: [
              _RecentQuizItem(
                subject: AppStrings.t('mathematics'),
                topic: AppStrings.t('trigonometry'),
                score: '10/10',
                rate: '100%',
                rateColor: const Color(0xFF10BA65),
              ),
              const SizedBox(height: 8),
              _RecentQuizItem(
                subject: AppStrings.t('science'),
                topic: AppStrings.t('planets_solar'),
                score: '8/10',
                rate: '80%',
                rateColor: const Color(0xFF6703BF),
              ),
            ],
          ),
        ),
      ),
    ],
  );
}

enum _StatBadge { quiz, accuracy, streak, score }

class _StatTile extends StatelessWidget {
  const _StatTile({
    required this.label,
    required this.value,
    required this.badge,
    required this.color,
  });

  final String label;
  final String value;
  final _StatBadge badge;
  final Color color;

  @override
  Widget build(BuildContext context) => Container(
    height: 72,
    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      border: Border.all(color: const Color(0x14000000)),
      boxShadow: const [
        BoxShadow(
          color: Color(0x0A000000),
          offset: Offset(0, 3),
          blurRadius: 8,
        ),
      ],
    ),
    child: Row(
      children: [
        Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Center(
            child: CustomPaint(
              size: const Size(20, 20),
              painter: _BadgePainter(badge, color),
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                value,
                style: TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: color,
                  height: 1.0,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 11,
                  fontWeight: FontWeight.w400,
                  color: Color(0xFF777777),
                  height: 1.0,
                ),
              ),
            ],
          ),
        ),
      ],
    ),
  );
}

class _RecentQuizItem extends StatelessWidget {
  const _RecentQuizItem({
    required this.subject,
    required this.topic,
    required this.score,
    required this.rate,
    required this.rateColor,
  });

  final String subject;
  final String topic;
  final String score;
  final String rate;
  final Color rateColor;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      border: Border.all(color: const Color(0x14000000)),
    ),
    child: Row(
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                subject,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF333333),
                  height: 1.2,
                ),
              ),
              Text(
                topic,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 11,
                  fontWeight: FontWeight.w400,
                  color: Color(0xFF888888),
                  height: 1.2,
                ),
              ),
            ],
          ),
        ),
        Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              score,
              style: TextStyle(
                fontFamily: 'Poppins',
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: rateColor,
                height: 1.2,
              ),
            ),
            Text(
              rate,
              style: const TextStyle(
                fontFamily: 'Poppins',
                fontSize: 11,
                fontWeight: FontWeight.w500,
                color: Color(0xFF999999),
                height: 1.2,
              ),
            ),
          ],
        ),
      ],
    ),
  );
}

class _BadgePainter extends CustomPainter {
  const _BadgePainter(this.badge, this.color);
  final _StatBadge badge;
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final strokePaint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.0
      ..strokeCap = StrokeCap.round;
    final fillPaint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;

    switch (badge) {
      case _StatBadge.quiz:
        // Question bubble / mark
        canvas.drawRRect(
          RRect.fromRectAndRadius(
            Rect.fromLTWH(2, 2, w - 4, h - 6),
            const Radius.circular(4),
          ),
          strokePaint,
        );
        final tail = Path()
          ..moveTo(6, h - 4)
          ..lineTo(3, h - 1)
          ..lineTo(9, h - 4);
        canvas.drawPath(tail, strokePaint);
        break;

      case _StatBadge.accuracy:
        // Bullseye target
        canvas.drawCircle(Offset(w / 2, h / 2), w / 2 - 2, strokePaint);
        canvas.drawCircle(Offset(w / 2, h / 2), 3, fillPaint);
        break;

      case _StatBadge.streak:
        // Flame shape
        final flame = Path()
          ..moveTo(w * 0.5, 2)
          ..cubicTo(w * 0.7, 6, w * 0.9, 10, w * 0.85, 14)
          ..cubicTo(w * 0.8, 18, w * 0.3, 19, w * 0.2, 14)
          ..cubicTo(w * 0.1, 10, w * 0.4, 7, w * 0.5, 2)
          ..close();
        canvas.drawPath(flame, fillPaint);
        break;

      case _StatBadge.score:
        // Star / Trophy symbol
        final star = Path()
          ..moveTo(w * 0.5, 2)
          ..lineTo(w * 0.62, 7)
          ..lineTo(w * 0.95, 7.5)
          ..lineTo(w * 0.7, 10.5)
          ..lineTo(w * 0.78, 16)
          ..lineTo(w * 0.5, 13)
          ..lineTo(w * 0.22, 16)
          ..lineTo(w * 0.3, 10.5)
          ..lineTo(w * 0.05, 7.5)
          ..lineTo(w * 0.38, 7)
          ..close();
        canvas.drawPath(star, fillPaint);
        break;
    }
  }

  @override
  bool shouldRepaint(covariant _BadgePainter oldDelegate) =>
      oldDelegate.badge != badge || oldDelegate.color != color;
}
