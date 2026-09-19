import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/attendance_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';
import '../widgets/shimmer_loading.dart';

/// Attendance is admin-controlled only (roadmap §7.5) — this screen is
/// read-only, showing what an admin has already marked.
class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  AttendanceRecord? _today;
  AttendanceSummary _summary = AttendanceSummary.empty;
  List<AttendanceRecord> _history = const [];
  bool _loading = true;
  bool _loadFailed = false;

  @override
  void initState() {
    super.initState();
    AppLanguage.instance.addListener(_onLanguageChanged);
    _load();
  }

  @override
  void dispose() {
    AppLanguage.instance.removeListener(_onLanguageChanged);
    super.dispose();
  }

  void _onLanguageChanged() {
    if (mounted) setState(() {});
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _loadFailed = false;
    });
    try {
      final results = await Future.wait([
        AttendanceService.instance.fetchToday(),
        AttendanceService.instance.fetchSummary(),
        AttendanceService.instance.fetchHistory(),
      ]);
      if (!mounted) return;
      setState(() {
        _today   = results[0] as AttendanceRecord?;
        _summary = results[1] as AttendanceSummary;
        _history = results[2] as List<AttendanceRecord>;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loadFailed = true;
        _loading    = false;
      });
    }
  }

  Color _statusColor(String status) => switch (status.toLowerCase()) {
        'present' => const Color(0xFF10BA65),
        'absent'  => const Color(0xFFE53935),
        'leave'   => const Color(0xFFFF9800),
        _         => const Color(0xFF8E8E93),
      };

  String _statusLabel(String status) => switch (status.toLowerCase()) {
        'present' => AppStrings.t('present'),
        'absent'  => AppStrings.t('absent'),
        'leave'   => AppStrings.t('leave'),
        _         => AppStrings.t('not_marked'),
      };

  IconData _statusIcon(String status) => switch (status.toLowerCase()) {
        'present' => Icons.check_circle_rounded,
        'absent'  => Icons.cancel_rounded,
        'leave'   => Icons.pause_circle_filled_rounded,
        _         => Icons.schedule_rounded,
      };

  // ── Height helpers ──────────────────────────────────────────────────────────
  static const double _headerCardY    = 192.0;
  static const double _cardPadding    =  20.0;
  static const double _todayCardH     =  98.0;
  static const double _summaryRowH    =  96.0;
  static const double _summaryWideH   =  96.0;
  static const double _sectionGap     =  14.0;
  static const double _historyRowH    =  68.0;
  static const double _historyGap     =  10.0;
  static const double _historyHeaderH =  34.0;

  double get _panelHeight {
    if (_loading) return 560;
    if (_loadFailed) return 230;
    final historyH = _history.isEmpty
        ? 220.0
        : _historyRowH * _history.length + (_history.length - 1) * _historyGap;
    return _cardPadding +
        _todayCardH +
        _sectionGap +
        _summaryRowH +
        _sectionGap +
        _summaryWideH +
        _sectionGap + 2 +
        _historyHeaderH +
        _sectionGap / 2 +
        historyH +
        _cardPadding + 6;
  }

  @override
  Widget build(BuildContext context) {
    double y = _headerCardY + _cardPadding;

    return DesignCanvas(
      adTop:   _headerCardY + _panelHeight + 24,
      adAfter: _headerCardY + _panelHeight + 16,
      color:   const Color(0xFFFCFAFE),
      bottomNav: QuizBottomNav(
        initialIndex: 2,
        onTabSelected: (index) {
          switch (index) {
            case 0:
              Navigator.pushNamedAndRemoveUntil(context, '/', (r) => false);
            case 1:
              Navigator.pushReplacementNamed(context, '/categories');
            case 3:
              Navigator.pushReplacementNamed(context, '/profile');
          }
        },
      ),
      children: [
        // Purple header
        at(0, -139, 412, 467, const PurpleHeader()),
        backButton(context),
        label(
          AppStrings.t('attendance_title'),
          0,
          116,
          28,
          width: 412,
          align: TextAlign.center,
          color: Colors.white,
          weight: FontWeight.w800,
        ),
        at(
          0,
          150,
          412,
          22,
          Center(
            child: Text(
              AppStrings.t('official_attendance_log'),
              style: const TextStyle(
                fontFamily: 'Quicksand',
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Color(0xDDFFFFFF),
                letterSpacing: 0.2,
              ),
            ),
          ),
        ),

        // White panel
        panel(0, _headerCardY, 412, _panelHeight, Colors.white, radius: 32),

        // ── LOADING shimmer ────────────────────────────────────────────────
        if (_loading) ...[
          at(
            _cardPadding,
            y,
            412 - _cardPadding * 2,
            _todayCardH,
            const _CardShimmer(height: 98, radius: 22),
          ),
          at(
            _cardPadding,
            y + _todayCardH + _sectionGap,
            412 - _cardPadding * 2,
            _summaryRowH,
            const Row(
              children: [
                Expanded(child: _CardShimmer(height: 96, radius: 18)),
                SizedBox(width: 10),
                Expanded(child: _CardShimmer(height: 96, radius: 18)),
                SizedBox(width: 10),
                Expanded(child: _CardShimmer(height: 96, radius: 18)),
              ],
            ),
          ),
          at(
            _cardPadding,
            y + _todayCardH + _sectionGap + _summaryRowH + _sectionGap,
            412 - _cardPadding * 2,
            _summaryWideH,
            const _CardShimmer(height: 96, radius: 20),
          ),
          for (var i = 0; i < 4; i++)
            at(
              _cardPadding,
              y +
                  _todayCardH +
                  _sectionGap +
                  _summaryRowH +
                  _sectionGap +
                  _summaryWideH +
                  _sectionGap + 2 +
                  _historyHeaderH +
                  _sectionGap / 2 +
                  i * (_historyRowH + _historyGap),
              412 - _cardPadding * 2,
              _historyRowH,
              _CardShimmer(height: _historyRowH, radius: 16),
            ),
        ],

        // ── LOAD FAILED ───────────────────────────────────────────────────
        if (_loadFailed)
          at(
            _cardPadding,
            y + 20,
            412 - _cardPadding * 2,
            180,
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 54,
                  height: 54,
                  decoration: BoxDecoration(
                    color: QuizColors.purple.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.wifi_off_rounded,
                      color: QuizColors.purple, size: 28),
                ),
                const SizedBox(height: 12),
                Text(
                  AppStrings.t('could_not_load_attendance'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF333333),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  AppStrings.t('check_internet_connection'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontFamily: 'Quicksand',
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF8E8E98),
                  ),
                ),
                const SizedBox(height: 14),
                GestureDetector(
                  onTap: _load,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 8),
                    decoration: BoxDecoration(
                      color: QuizColors.purple,
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(
                          color: QuizColors.purple.withValues(alpha: 0.3),
                          blurRadius: 10,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Text(
                      AppStrings.t('retry'),
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

        // ── LOADED CONTENT ────────────────────────────────────────────────
        if (!_loading && !_loadFailed) ...[
          // Today's Status Hero Card
          at(
            _cardPadding,
            y,
            412 - _cardPadding * 2,
            _todayCardH,
            AnimatedSection(
              delay: const Duration(milliseconds: 40),
              child: _TodayCard(
                label: _statusLabel(_today?.status ?? ''),
                color: _statusColor(_today?.status ?? ''),
                icon: _statusIcon(_today?.status ?? ''),
                date: _today?.date ?? AppStrings.t('today'),
              ),
            ),
          ),

          // Summary Tiles (Present, Absent, Leave)
          at(
            _cardPadding,
            y + _todayCardH + _sectionGap,
            412 - _cardPadding * 2,
            _summaryRowH,
            AnimatedSection(
              delay: const Duration(milliseconds: 80),
              child: Row(
                children: [
                  Expanded(
                    child: _SummaryTile(
                      label: AppStrings.t('present'),
                      value: '${_summary.presentDays}',
                      color: _statusColor('present'),
                      icon: Icons.check_circle_outline_rounded,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _SummaryTile(
                      label: AppStrings.t('absent'),
                      value: '${_summary.absentDays}',
                      color: _statusColor('absent'),
                      icon: Icons.highlight_off_rounded,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _SummaryTile(
                      label: AppStrings.t('leave'),
                      value: '${_summary.leaveDays}',
                      color: _statusColor('leave'),
                      icon: Icons.pause_circle_outline_rounded,
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Overall Attendance Performance Card with progress bar
          at(
            _cardPadding,
            y + _todayCardH + _sectionGap + _summaryRowH + _sectionGap,
            412 - _cardPadding * 2,
            _summaryWideH,
            AnimatedSection(
              delay: const Duration(milliseconds: 110),
              child: _AttendanceRateCard(
                percentage: _summary.attendancePercentage,
                presentDays: _summary.presentDays,
                totalDays: _summary.presentDays + _summary.absentDays + _summary.leaveDays,
              ),
            ),
          ),

          // History Section Header
          at(
            _cardPadding,
            y + _todayCardH + _sectionGap + _summaryRowH + _sectionGap + _summaryWideH + _sectionGap + 2,
            412 - _cardPadding * 2,
            _historyHeaderH,
            AnimatedSection(
              delay: const Duration(milliseconds: 130),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 28,
                        height: 28,
                        decoration: BoxDecoration(
                          color: QuizColors.purple.withValues(alpha: 0.1),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.history_rounded,
                          color: QuizColors.purple,
                          size: 17,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        AppStrings.t('attendance_log'),
                        style: const TextStyle(
                          fontFamily: 'Poppins',
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                          color: QuizColors.purple,
                          letterSpacing: -0.3,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF3E8FF),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          '${_history.length}',
                          style: const TextStyle(
                            fontFamily: 'Quicksand',
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: QuizColors.purple,
                          ),
                        ),
                      ),
                    ],
                  ),
                  Material(
                    color: Colors.transparent,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(16),
                      onTap: _load,
                      child: Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: QuizColors.purple.withValues(alpha: 0.08),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: QuizColors.purple.withValues(alpha: 0.18),
                            width: 1,
                          ),
                        ),
                        child: const Icon(
                          Icons.refresh_rounded,
                          color: QuizColors.purple,
                          size: 18,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // History list or empty state
          if (_history.isEmpty)
            at(
              _cardPadding,
              y +
                  _todayCardH +
                  _sectionGap +
                  _summaryRowH +
                  _sectionGap +
                  _summaryWideH +
                  _sectionGap + 2 +
                  _historyHeaderH +
                  _sectionGap / 2,
              412 - _cardPadding * 2,
              220,
              AnimatedSection(
                delay: const Duration(milliseconds: 150),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF8FD),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFEFE8F8)),
                  ),
                  child: NoDataView(
                    message: AppStrings.t('no_attendance_recorded'),
                    subMessage: AppStrings.t('no_attendance_sub'),
                    imageSize: 84,
                  ),
                ),
              ),
            )
          else
            for (var i = 0; i < _history.length; i++)
              at(
                _cardPadding,
                y +
                    _todayCardH +
                    _sectionGap +
                    _summaryRowH +
                    _sectionGap +
                    _summaryWideH +
                    _sectionGap + 2 +
                    _historyHeaderH +
                    _sectionGap / 2 +
                    i * (_historyRowH + _historyGap),
                412 - _cardPadding * 2,
                _historyRowH,
                AnimatedSection(
                  delay: Duration(milliseconds: 150 + i * 40),
                  child: _HistoryRow(
                    record: _history[i],
                    color:  _statusColor(_history[i].status),
                    label:  _statusLabel(_history[i].status),
                    icon:   _statusIcon(_history[i].status),
                  ),
                ),
              ),
        ],
      ],
    );
  }
}

// ── Shimmer placeholder card ─────────────────────────────────────────────────
class _CardShimmer extends StatelessWidget {
  const _CardShimmer({required this.height, this.radius = 18});
  final double height;
  final double radius;

  @override
  Widget build(BuildContext context) => Shimmer(
        baseColor: const Color(0x18BF00FF),
        highlightColor: const Color(0x35FFFFFF),
        child: Container(
          height: height,
          decoration: BoxDecoration(
            color: const Color(0x12BF00FF),
            borderRadius: BorderRadius.circular(radius),
          ),
        ),
      );
}

// ── Today's Attendance Hero Card ─────────────────────────────────────────────
class _TodayCard extends StatelessWidget {
  const _TodayCard({
    required this.label,
    required this.color,
    required this.icon,
    required this.date,
  });

  final String label;
  final Color color;
  final IconData icon;
  final String date;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Colors.white,
              color.withValues(alpha: 0.06),
            ],
          ),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.12),
              blurRadius: 18,
              offset: const Offset(0, 6),
            ),
            const BoxShadow(
              color: Color(0x06000000),
              blurRadius: 6,
              offset: Offset(0, 2),
            ),
          ],
          border: Border.all(color: color.withValues(alpha: 0.26), width: 1.4),
        ),
        child: Row(
          children: [
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(15),
                border: Border.all(color: color.withValues(alpha: 0.28), width: 1.2),
              ),
              child: Icon(icon, color: color, size: 26),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          color: color,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          AppStrings.t('todays_status'),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontFamily: 'Quicksand',
                            fontSize: 10.5,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.5,
                            color: Color(0xFF7A7A8E),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(
                    label,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 19,
                      fontWeight: FontWeight.w800,
                      color: color,
                      letterSpacing: -0.2,
                      height: 1.15,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      const Icon(
                        Icons.event_note_rounded,
                        size: 13,
                        color: Color(0xFF9E9EAA),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          date.isNotEmpty ? date : AppStrings.t('official_record'),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontFamily: 'Quicksand',
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF888898),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: color.withValues(alpha: 0.3), width: 1.2),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(icon, size: 13, color: color),
                  const SizedBox(width: 4),
                  Text(
                    label.toUpperCase(),
                    style: TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.4,
                      color: color,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
}

// ── Summary Tile (Present, Absent, Leave) ─────────────────────────────────────
class _SummaryTile extends StatelessWidget {
  const _SummaryTile({
    required this.label,
    required this.value,
    required this.color,
    required this.icon,
  });

  final String label;
  final String value;
  final Color color;
  final IconData icon;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Colors.white,
              color.withValues(alpha: 0.08),
            ],
          ),
          border: Border.all(color: color.withValues(alpha: 0.25), width: 1.2),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.10),
              blurRadius: 14,
              offset: const Offset(0, 4),
            ),
            const BoxShadow(
              color: Color(0x05000000),
              blurRadius: 4,
              offset: Offset(0, 1),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon, size: 14, color: color),
                const SizedBox(width: 4),
                Flexible(
                  child: FittedBox(
                    fit: BoxFit.scaleDown,
                    child: Text(
                      label,
                      style: const TextStyle(
                        fontFamily: 'Quicksand',
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF333333),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontFamily: 'Poppins',
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: color,
                    height: 1.0,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(width: 2),
                Text(
                  AppStrings.t('days_short'),
                  style: TextStyle(
                    fontFamily: 'Quicksand',
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: color.withValues(alpha: 0.75),
                  ),
                ),
              ],
            ),
            Container(
              width: 24,
              height: 3,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.4),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ],
        ),
      );
}

// ── Overall Attendance Performance Card ──────────────────────────────────────
class _AttendanceRateCard extends StatelessWidget {
  const _AttendanceRateCard({
    required this.percentage,
    required this.presentDays,
    required this.totalDays,
  });

  final double percentage;
  final int presentDays;
  final int totalDays;

  @override
  Widget build(BuildContext context) {
    final cleanPercent = percentage.clamp(0.0, 100.0);
    final isGood = cleanPercent >= 75.0;
    final isMedium = cleanPercent >= 60.0 && cleanPercent < 75.0;
    final badgeColor = isGood
        ? const Color(0xFF10BA65)
        : (isMedium ? const Color(0xFFFF9800) : const Color(0xFFE53935));
    final badgeLabel = isGood
        ? AppStrings.t('great')
        : (isMedium ? AppStrings.t('average') : AppStrings.t('low'));

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Colors.white,
            Color(0xFFFAF7FE),
          ],
        ),
        border: Border.all(color: const Color(0xFFE5DBF5), width: 1.2),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0F53009C),
            blurRadius: 16,
            offset: Offset(0, 5),
          ),
          BoxShadow(
            color: Color(0x05000000),
            blurRadius: 4,
            offset: Offset(0, 1),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: QuizColors.purple.withValues(alpha: 0.09),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: QuizColors.purple.withValues(alpha: 0.16),
                width: 1,
              ),
            ),
            child: const Icon(
              Icons.analytics_rounded,
              color: QuizColors.purple,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Row(
                  children: [
                    Text(
                      AppStrings.t('overall_rate'),
                      style: const TextStyle(
                        fontFamily: 'Poppins',
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A1A24),
                        letterSpacing: -0.2,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: badgeColor.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 5,
                            height: 5,
                            decoration: BoxDecoration(
                              color: badgeColor,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            badgeLabel,
                            style: TextStyle(
                              fontFamily: 'Quicksand',
                              fontSize: 10.5,
                              fontWeight: FontWeight.w700,
                              color: badgeColor,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 7),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: SizedBox(
                    height: 7,
                    child: Stack(
                      children: [
                        Container(
                          color: const Color(0xFFEBE4F5),
                        ),
                        FractionallySizedBox(
                          alignment: Alignment.centerLeft,
                          widthFactor: cleanPercent / 100.0,
                          child: Container(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: isGood
                                    ? [const Color(0xFF10BA65), const Color(0xFF34D399)]
                                    : [QuizColors.purple, const Color(0xFF8B2CE2)],
                              ),
                              borderRadius: BorderRadius.circular(4),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  totalDays > 0
                      ? '$presentDays ${AppStrings.t('of_word')} $totalDays ${AppStrings.t('sessions_attended')}'
                      : AppStrings.t('updated_by_admin'),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontFamily: 'Quicksand',
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF7A7A8E),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Text(
            '${cleanPercent.round()}%',
            style: const TextStyle(
              fontFamily: 'Poppins',
              fontSize: 25,
              fontWeight: FontWeight.w800,
              color: QuizColors.purple,
              letterSpacing: -0.6,
            ),
          ),
        ],
      ),
    );
  }
}

// ── History Row Item ──────────────────────────────────────────────────────────
class _HistoryRow extends StatelessWidget {
  const _HistoryRow({
    required this.record,
    required this.color,
    required this.label,
    required this.icon,
  });

  final AttendanceRecord record;
  final Color color;
  final String label;
  final IconData icon;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFECE5F5), width: 1.1),
          boxShadow: const [
            BoxShadow(
              color: Color(0x07000000),
              blurRadius: 8,
              offset: Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: color.withValues(alpha: 0.22), width: 1),
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    record.date,
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1E1E28),
                      letterSpacing: -0.1,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    record.note != null && record.note!.isNotEmpty
                        ? record.note!
                        : AppStrings.t('official_attendance_entry'),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontFamily: 'Quicksand',
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF888896),
                    ),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.11),
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: color.withValues(alpha: 0.26), width: 1),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 5,
                    height: 5,
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 5),
                  Text(
                    label,
                    style: TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 11.5,
                      fontWeight: FontWeight.w700,
                      color: color,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
}
