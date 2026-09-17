import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/attendance_service.dart';
import '../widgets/design_widgets.dart';
import '../widgets/quiz_bottom_nav.dart';

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
    _load();
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
        _today = results[0] as AttendanceRecord?;
        _summary = results[1] as AttendanceSummary;
        _history = results[2] as List<AttendanceRecord>;
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

  Color _statusColor(String status) => switch (status) {
        'present' => const Color(0xFF10BA65),
        'absent' => const Color(0xFFE53935),
        'leave' => const Color(0xFFFF9800),
        _ => const Color(0xFF9E9E9E),
      };

  String _statusLabel(String status) => switch (status) {
        'present' => 'Present',
        'absent' => 'Absent',
        'leave' => 'Leave',
        _ => 'Not marked',
      };

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: const Color(0xFFF8F5FC),
        bottomNavigationBar: SafeArea(
          minimum: const EdgeInsets.only(bottom: 14),
          child: Center(
            child: QuizBottomNav(
              initialIndex: 3,
              onTabSelected: (index) {
                switch (index) {
                  case 0:
                    Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
                  case 1:
                    Navigator.pushReplacementNamed(context, '/categories');
                  case 2:
                    Navigator.pushReplacementNamed(context, '/profile');
                }
              },
            ),
          ),
        ),
        body: SafeArea(
          bottom: false,
          child: RefreshIndicator(
            onRefresh: _load,
            color: QuizColors.purple,
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: QuizColors.purple))
                : ListView(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
                    children: [
                      Text(
                        AppStrings.t('attendance'),
                        style: const TextStyle(
                          fontFamily: 'Poppins',
                          fontSize: 22,
                          fontWeight: FontWeight.w700,
                          color: QuizColors.purple,
                        ),
                      ),
                      const SizedBox(height: 20),
                      if (_loadFailed)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 24),
                          child: Text(
                            'Could not load attendance. Pull down to retry.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontFamily: 'Poppins', color: Color(0xFF757575)),
                          ),
                        )
                      else ...[
                        _TodayCard(
                          status: _today?.status,
                          label: _statusLabel(_today?.status ?? ''),
                          color: _statusColor(_today?.status ?? ''),
                        ),
                        const SizedBox(height: 20),
                        Row(
                          children: [
                            Expanded(child: _SummaryTile(label: 'Present', value: '${_summary.presentDays}', color: _statusColor('present'))),
                            const SizedBox(width: 10),
                            Expanded(child: _SummaryTile(label: 'Absent', value: '${_summary.absentDays}', color: _statusColor('absent'))),
                            const SizedBox(width: 10),
                            Expanded(child: _SummaryTile(label: 'Leave', value: '${_summary.leaveDays}', color: _statusColor('leave'))),
                          ],
                        ),
                        const SizedBox(height: 10),
                        _SummaryTile(
                          label: 'Attendance Percentage',
                          value: '${_summary.attendancePercentage.round()}%',
                          color: QuizColors.purple,
                          wide: true,
                        ),
                        const SizedBox(height: 24),
                        const Text(
                          'History',
                          style: TextStyle(
                            fontFamily: 'Poppins',
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1E1E1E),
                          ),
                        ),
                        const SizedBox(height: 10),
                        if (_history.isEmpty)
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 16),
                            child: Text(
                              'No attendance recorded yet.',
                              style: TextStyle(fontFamily: 'Poppins', color: Color(0xFF9E9E9E)),
                            ),
                          )
                        else
                          ..._history.map((record) => _HistoryRow(
                                record: record,
                                color: _statusColor(record.status),
                                label: _statusLabel(record.status),
                              )),
                      ],
                    ],
                  ),
          ),
        ),
      );
}

class _TodayCard extends StatelessWidget {
  const _TodayCard({required this.status, required this.label, required this.color});

  final String? status;
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: const [BoxShadow(color: Color(0x14000000), blurRadius: 12, offset: Offset(0, 4))],
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(color: color.withValues(alpha: 0.12), shape: BoxShape.circle),
              child: Icon(Icons.event_available_rounded, color: color, size: 26),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    "Today's Attendance",
                    style: TextStyle(fontFamily: 'Poppins', fontSize: 12.5, color: Color(0xFF757575)),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    label,
                    style: TextStyle(fontFamily: 'Poppins', fontSize: 17, fontWeight: FontWeight.w700, color: color),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
}

class _SummaryTile extends StatelessWidget {
  const _SummaryTile({required this.label, required this.value, required this.color, this.wide = false});

  final String label;
  final String value;
  final Color color;
  final bool wide;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFEAE5F2)),
        ),
        child: wide
            ? Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(label, style: const TextStyle(fontFamily: 'Poppins', fontSize: 13, color: Color(0xFF757575))),
                  Text(value, style: TextStyle(fontFamily: 'Poppins', fontSize: 18, fontWeight: FontWeight.w700, color: color)),
                ],
              )
            : Column(
                children: [
                  Text(value, style: TextStyle(fontFamily: 'Poppins', fontSize: 20, fontWeight: FontWeight.w700, color: color)),
                  const SizedBox(height: 4),
                  Text(label, style: const TextStyle(fontFamily: 'Poppins', fontSize: 11.5, color: Color(0xFF757575))),
                ],
              ),
      );
}

class _HistoryRow extends StatelessWidget {
  const _HistoryRow({required this.record, required this.color, required this.label});

  final AttendanceRecord record;
  final Color color;
  final String label;

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFF0EBF6)),
        ),
        child: Row(
          children: [
            Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
            const SizedBox(width: 10),
            Expanded(
              child: Text(record.date, style: const TextStyle(fontFamily: 'Poppins', fontSize: 13, color: Color(0xFF1E1E1E))),
            ),
            Text(label, style: TextStyle(fontFamily: 'Poppins', fontSize: 13, fontWeight: FontWeight.w600, color: color)),
          ],
        ),
      );
}
