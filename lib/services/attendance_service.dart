import 'api_client.dart';

/// One day's attendance record, as marked by an admin (roadmap §7.5 —
/// attendance is admin-controlled only; the app never sets it).
class AttendanceRecord {
  const AttendanceRecord({required this.date, required this.status, this.note});

  final String date;

  /// 'present' | 'absent' | 'leave'.
  final String status;
  final String? note;

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) => AttendanceRecord(
        date: json['date'] as String? ?? '',
        status: json['status'] as String? ?? '',
        note: json['note'] as String?,
      );
}

class AttendanceSummary {
  const AttendanceSummary({
    required this.presentDays,
    required this.absentDays,
    required this.leaveDays,
    required this.attendancePercentage,
  });

  final int presentDays;
  final int absentDays;
  final int leaveDays;

  /// 0-100.
  final double attendancePercentage;

  static const empty = AttendanceSummary(
    presentDays: 0,
    absentDays: 0,
    leaveDays: 0,
    attendancePercentage: 0,
  );

  factory AttendanceSummary.fromJson(Map<String, dynamic> json) => AttendanceSummary(
        presentDays: (json['present_days'] as num?)?.toInt() ?? 0,
        absentDays: (json['absent_days'] as num?)?.toInt() ?? 0,
        leaveDays: (json['leave_days'] as num?)?.toInt() ?? 0,
        attendancePercentage: (json['attendance_percentage'] as num?)?.toDouble() ?? 0,
      );
}

class AttendanceService {
  AttendanceService._();
  static final AttendanceService instance = AttendanceService._();

  /// Null means today hasn't been marked by an admin yet.
  Future<AttendanceRecord?> fetchToday() async {
    final data = await ApiClient.instance.get('/attendance/today');
    final attendance = data['attendance'] as Map<String, dynamic>?;
    return attendance == null ? null : AttendanceRecord.fromJson(attendance);
  }

  Future<AttendanceSummary> fetchSummary() async {
    final data = await ApiClient.instance.get('/attendance/summary');
    final summary = data['summary'] as Map<String, dynamic>? ?? {};
    return AttendanceSummary.fromJson(summary);
  }

  Future<List<AttendanceRecord>> fetchHistory() async {
    final data = await ApiClient.instance.get('/attendance/history');
    final history = data['history'] as Map<String, dynamic>?;
    final rows = (history?['data'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
    return rows.map(AttendanceRecord.fromJson).toList();
  }
}
