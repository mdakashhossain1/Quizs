import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/achievement_service.dart';
import '../services/auth_service.dart';
import '../widgets/design_widgets.dart';

/// Standalone Achievement / Global Ranking page (leaderboard-achievement
/// roadmap Part B) — deliberately its own screen rather than a merge into
/// [ProfileScreen], since it shows site-wide ranking data the Profile
/// screen never needs.
class AchievementsScreen extends StatefulWidget {
  const AchievementsScreen({super.key});

  @override
  State<AchievementsScreen> createState() => _AchievementsScreenState();
}

class _AchievementsScreenState extends State<AchievementsScreen> {
  AchievementSummary _summary = AchievementSummary.empty;
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
      final summary = await AchievementService.instance.fetch();
      if (!mounted) return;
      setState(() {
        _summary = summary;
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

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: const Color(0xFFF8F5FC),
        body: SafeArea(
          child: RefreshIndicator(
            onRefresh: _load,
            color: QuizColors.purple,
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: QuizColors.purple))
                : ListView(
                    padding: const EdgeInsets.fromLTRB(20, 12, 20, 40),
                    children: [
                      Row(
                        children: [
                          GestureDetector(
                            onTap: () => Navigator.of(context).pop(),
                            child: Container(
                              width: 36,
                              height: 36,
                              decoration: const BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                boxShadow: [BoxShadow(color: Color(0x14000000), blurRadius: 8)],
                              ),
                              child: const Icon(Icons.arrow_back_ios_new_rounded, size: 16, color: QuizColors.purple),
                            ),
                          ),
                          const SizedBox(width: 14),
                          Text(
                            AppStrings.t('achievements_title'),
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                              color: QuizColors.purple,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),
                      if (_loadFailed)
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 24),
                          child: Text(
                            'Could not load achievements. Pull down to retry.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontFamily: 'Poppins', color: Color(0xFF757575)),
                          ),
                        )
                      else ...[
                        Center(
                          child: SizedBox(
                            width: 110,
                            height: 110,
                            child: Stack(
                              alignment: Alignment.center,
                              children: [
                                DailyProgressRing(progress: _summary.todayProgressPercentage / 100, size: 110),
                                CircleAvatar(
                                  radius: 40,
                                  backgroundColor: const Color(0xFFEDE4F7),
                                  backgroundImage: (AuthService.instance.userPhoto?.isNotEmpty ?? false)
                                      ? NetworkImage(AuthService.instance.userPhoto!)
                                      : null,
                                  child: (AuthService.instance.userPhoto?.isNotEmpty ?? false)
                                      ? null
                                      : const Icon(Icons.person, size: 42, color: QuizColors.purple),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 10),
                        Center(
                          child: Text(
                            _summary.name.isNotEmpty ? _summary.name : AuthService.instance.userName,
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 17,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E1E1E),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            _Badge(value: '${_summary.level}', label: AppStrings.t('level_label')),
                            const SizedBox(width: 14),
                            _Badge(value: '${_summary.accuracy.round()}%', label: AppStrings.t('accuracy_label')),
                          ],
                        ),
                        const SizedBox(height: 24),
                        Row(
                          children: [
                            Expanded(
                              child: _StatCard(
                                value: _summary.yourRank != null ? '#${_summary.yourRank}' : '—',
                                label: AppStrings.t('your_rank_stat'),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _StatCard(
                                value: '${_summary.totalEligibleUsers}',
                                label: AppStrings.t('total_users_stat'),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Expanded(
                              child: _StatCard(
                                value: '${_summary.activeUsers}',
                                label: AppStrings.t('active_users_stat'),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _StatCard(
                                value: '${_summary.activeThisMonth}',
                                label: AppStrings.t('active_this_month_stat'),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),
                        Text(
                          AppStrings.t('global_ranking'),
                          style: const TextStyle(
                            fontFamily: 'Poppins',
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1E1E1E),
                          ),
                        ),
                        const SizedBox(height: 10),
                        if (_summary.top.isEmpty)
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            child: Text(
                              AppStrings.t('no_ranking_data'),
                              style: const TextStyle(fontFamily: 'Poppins', color: Color(0xFF9E9E9E)),
                            ),
                          )
                        else
                          ..._summary.top.map(
                            (entry) => _RankingRow(
                              entry: entry,
                              isCurrentUser: entry.rank == _summary.yourRank,
                            ),
                          ),
                      ],
                    ],
                  ),
          ),
        ),
      );
}

class _Badge extends StatelessWidget {
  const _Badge({required this.value, required this.label});

  final String value;
  final String label;

  @override
  Widget build(BuildContext context) => Container(
        width: 72,
        height: 72,
        decoration: const BoxDecoration(color: QuizColors.purple, shape: BoxShape.circle),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              value,
              style: const TextStyle(fontFamily: 'Poppins', fontSize: 18, fontWeight: FontWeight.w700, color: Colors.white),
            ),
            Text(
              label,
              style: const TextStyle(fontFamily: 'Poppins', fontSize: 10, fontWeight: FontWeight.w500, color: Colors.white),
            ),
          ],
        ),
      );
}

class _StatCard extends StatelessWidget {
  const _StatCard({required this.value, required this.label});

  final String value;
  final String label;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFEAE5F2)),
        ),
        child: Column(
          children: [
            Text(value, style: const TextStyle(fontFamily: 'Poppins', fontSize: 18, fontWeight: FontWeight.w700, color: QuizColors.purple)),
            const SizedBox(height: 4),
            Text(
              label,
              textAlign: TextAlign.center,
              style: const TextStyle(fontFamily: 'Poppins', fontSize: 11.5, color: Color(0xFF757575)),
            ),
          ],
        ),
      );
}

class _RankingRow extends StatelessWidget {
  const _RankingRow({required this.entry, required this.isCurrentUser});

  final AchievementRankingEntry entry;
  final bool isCurrentUser;

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
        decoration: BoxDecoration(
          color: isCurrentUser ? const Color(0xFFF3EBFA) : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isCurrentUser ? QuizColors.purple : const Color(0xFFF0EBF6)),
        ),
        child: Row(
          children: [
            Container(
              width: 28,
              height: 28,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: isCurrentUser ? QuizColors.purple : const Color(0xFFF3EEF8),
                shape: BoxShape.circle,
              ),
              child: Text(
                '${entry.rank}',
                style: TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: isCurrentUser ? Colors.white : QuizColors.purple,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                entry.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontFamily: 'Poppins', fontSize: 13.5, fontWeight: FontWeight.w600, color: Color(0xFF1E1E1E)),
              ),
            ),
            Text(
              'Lv.${entry.level}',
              style: const TextStyle(fontFamily: 'Poppins', fontSize: 12, fontWeight: FontWeight.w600, color: QuizColors.purple),
            ),
            const SizedBox(width: 10),
            Text(
              '${entry.accuracy.round()}%',
              style: const TextStyle(fontFamily: 'Poppins', fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF10BA65)),
            ),
          ],
        ),
      );
}
