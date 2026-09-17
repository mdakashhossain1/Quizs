<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Global/Achievement-page ranking — deliberately separate from
 * QuizRankingService (one quiz's own leaderboard). One authoritative
 * ordering computed here and reused everywhere a rank number is shown
 * (Profile, Achievement, admin), so they can never disagree
 * (leaderboard-achievement roadmap §26 "Data Consistency").
 */
class RankingService
{
    public static function rankFor(User $user): int
    {
        $ranked = self::fullRanking();
        $index = $ranked->search(fn (array $row) => $row['user_id'] === $user->id);

        return $index === false ? $ranked->count() + 1 : $index + 1;
    }

    /**
     * Top N plus the given user's own position — the Achievement page's
     * single data source for its ranking list + "Your Rank" summary.
     */
    public static function summaryFor(User $user, int $limit = 20): array
    {
        $ranked = self::fullRanking();
        $index = $ranked->search(fn (array $row) => $row['user_id'] === $user->id);

        return [
            'top' => $ranked->take($limit)->values()->all(),
            'your_rank' => $index === false ? null : $index + 1,
            'total_eligible_users' => $ranked->count(),
        ];
    }

    public static function totalEligibleUsers(): int
    {
        return User::eligible()->count();
    }

    /**
     * One row per eligible user, ordered best-first using the roadmap's
     * recommended hierarchy: level, then accumulated XP, then overall
     * accuracy, then completed-quiz count, then user id as a fully
     * deterministic final tie-break.
     *
     * @return Collection<int, array{user_id: int, name: string, avatar: ?string, level: int, xp: int, accuracy: float, quiz_played: int, score: int}>
     */
    private static function fullRanking(): Collection
    {
        $users = User::eligible()
            ->leftJoin('user_progression', 'user_progression.user_id', '=', 'users.id')
            ->select([
                'users.id as user_id',
                'users.name',
                'users.avatar',
                'users.score',
                DB::raw('COALESCE(user_progression.current_level, 1) as current_level'),
                DB::raw('COALESCE(user_progression.xp, 0) as xp'),
            ])
            ->get();

        $stats = DB::table('quiz_attempts')
            ->where('status', 'completed')
            ->select([
                'user_id',
                DB::raw('COUNT(*) as quiz_played'),
                DB::raw('SUM(correct_answers) as total_correct'),
                DB::raw('SUM(correct_answers + wrong_answers) as total_answered'),
            ])
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return $users
            ->map(function ($user) use ($stats) {
                $userStats = $stats->get($user->user_id);
                $answered = (int) ($userStats->total_answered ?? 0);
                $correct = (int) ($userStats->total_correct ?? 0);

                return [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'level' => (int) $user->current_level,
                    'xp' => (int) $user->xp,
                    'accuracy' => $answered > 0 ? round($correct / $answered * 100, 2) : 0.0,
                    'quiz_played' => (int) ($userStats->quiz_played ?? 0),
                    'score' => (int) $user->score,
                ];
            })
            ->sort(fn (array $a, array $b) => self::compare($a, $b))
            ->values();
    }

    /** Negative if $a ranks better than $b. */
    private static function compare(array $a, array $b): int
    {
        if ($a['level'] !== $b['level']) {
            return $b['level'] <=> $a['level'];
        }
        if ($a['xp'] !== $b['xp']) {
            return $b['xp'] <=> $a['xp'];
        }
        if ($a['accuracy'] !== $b['accuracy']) {
            return $b['accuracy'] <=> $a['accuracy'];
        }
        if ($a['quiz_played'] !== $b['quiz_played']) {
            return $b['quiz_played'] <=> $a['quiz_played'];
        }

        // Final deterministic tie-break: no two users can tie forever.
        return $a['user_id'] <=> $b['user_id'];
    }
}
