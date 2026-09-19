<?php

namespace App\Services;

use App\Models\QuizAttempt;
use Illuminate\Support\Collection;

/**
 * Per-quiz leaderboard — deliberately separate from RankingService (the
 * global/Achievement-page ranking). The two must never be conflated
 * (leaderboard-achievement roadmap Part C): this one only ever compares
 * users who attempted the SAME quiz, using that quiz's own scores.
 */
class QuizRankingService
{
    /**
     * Top N plus the given user's own position, computed from one pass over
     * the quiz's completed attempts. A user with multiple attempts is
     * represented once, by their best attempt (roadmap §8) — never several
     * leaderboard rows for the same person.
     */
    public static function summaryFor(int $quizId, int $userId, int $limit = 10): array
    {
        $ranked = self::bestAttemptPerUser($quizId);

        $yourIndex = $ranked->search(fn (QuizAttempt $a) => $a->user_id === $userId);

        return [
            'top' => $ranked->take($limit)->values()
                ->map(fn (QuizAttempt $a, int $i) => self::formatRow($a, $i + 1))
                ->all(),
            'your_rank' => $yourIndex === false ? null : $yourIndex + 1,
            'total_participants' => $ranked->count(),
        ];
    }

    /**
     * Site-wide version of [summaryFor] — same {top, your_rank,
     * total_participants} shape (the app's ResultsScreen renders both with
     * the same podium/leaderboard UI), but ranking every user by their
     * totals across ALL completed quizzes instead of one quiz's attempts.
     * Also returns the requesting user's own aggregate totals, so the
     * screen can show "your" solved/right/wrong exactly like it does right
     * after finishing a single quiz.
     */
    public static function globalSummaryFor(int $userId, int $limit = 10): array
    {
        $totals = QuizAttempt::where('status', 'completed')
            // LEAST(...) guards against a completed attempt whose cached
            // correct_answers outlived its answer rows (e.g. a question got
            // deleted afterward and cascade-deleted them, roadmap data-
            // integrity gap) — such a row can never contribute more correct
            // answers than it has solved questions.
            ->selectRaw('user_id, SUM(attempted_questions) as total_solved, SUM(LEAST(correct_answers, attempted_questions)) as total_correct, SUM(wrong_answers) as total_wrong')
            ->groupBy('user_id')
            ->with('user:id,name,avatar')
            ->get()
            // Correct-answer count is the primary ranking signal; total
            // solved only breaks a tie between two users with the same
            // correct count.
            ->sortByDesc(fn ($row) => ((int) $row->total_correct * 1_000_000) + (int) $row->total_solved)
            ->values();

        $yourIndex = $totals->search(fn ($row) => (int) $row->user_id === $userId);
        $yourRow = $yourIndex === false ? null : $totals[$yourIndex];
        $yourSolved = (int) ($yourRow->total_solved ?? 0);
        $yourCorrect = (int) ($yourRow->total_correct ?? 0);
        $yourWrong = (int) ($yourRow->total_wrong ?? 0);

        return [
            'total_questions' => $yourSolved,
            'correct_answers' => $yourCorrect,
            'wrong_answers' => $yourWrong,
            'accuracy' => $yourSolved > 0 ? round($yourCorrect / $yourSolved * 100, 2) : 0,
            'ranking' => [
                'top' => $totals->take($limit)->values()
                    ->map(fn ($row, int $i) => self::formatGlobalRow($row, $i + 1))
                    ->all(),
                'your_rank' => $yourIndex === false ? null : $yourIndex + 1,
                'total_participants' => $totals->count(),
            ],
        ];
    }

    /** @param object{user_id: int, total_solved: int, total_correct: int, total_wrong: int, user: ?\App\Models\User} $row */
    private static function formatGlobalRow(object $row, int $rank): array
    {
        $solved = (int) $row->total_solved;
        $correct = (int) $row->total_correct;

        return [
            'rank' => $rank,
            'user_id' => (int) $row->user_id,
            'name' => $row->user->name ?? 'Unknown',
            'avatar' => $row->user->avatar ?? null,
            'score' => $correct,
            'correct_answers' => $correct,
            'wrong_answers' => (int) $row->total_wrong,
            'accuracy' => $solved > 0 ? round($correct / $solved * 100, 2) : 0,
            'time_taken_seconds' => null,
        ];
    }

    /**
     * One row per user: their best completed attempt for this quiz, sorted
     * best-first using the roadmap's recommended tie-break order (score,
     * then accuracy, then lower completion time, then earlier completion).
     *
     * @return Collection<int, QuizAttempt>
     */
    private static function bestAttemptPerUser(int $quizId): Collection
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('status', 'completed')
            ->with('user:id,name,avatar')
            ->get()
            ->groupBy('user_id')
            ->map(function (Collection $attempts) {
                return $attempts->reduce(
                    fn (?QuizAttempt $best, QuizAttempt $a) => $best === null || self::compare($a, $best) < 0 ? $a : $best,
                );
            })
            ->sort(fn (QuizAttempt $a, QuizAttempt $b) => self::compare($a, $b))
            ->values();
    }

    /** Negative if $a ranks better than $b. */
    private static function compare(QuizAttempt $a, QuizAttempt $b): int
    {
        if ($a->score !== $b->score) {
            return $b->score <=> $a->score;
        }

        // accuracy is nullable (attempts created before it existed have no
        // value) — treat a missing accuracy as worse than any real one
        // rather than letting PHP's null<=>float comparison silently rank
        // it as 0, which would put unscored legacy attempts ahead of a
        // genuinely low-accuracy new attempt.
        $aAccuracy = $a->accuracy ?? -1.0;
        $bAccuracy = $b->accuracy ?? -1.0;
        if ($aAccuracy !== $bAccuracy) {
            return $bAccuracy <=> $aAccuracy;
        }

        $aTime = self::timeTakenSeconds($a);
        $bTime = self::timeTakenSeconds($b);
        if ($aTime !== $bTime) {
            return $aTime <=> $bTime;
        }

        // Final deterministic tie-breaker: whoever finished first.
        return $a->completed_at <=> $b->completed_at;
    }

    public static function timeTakenSeconds(QuizAttempt $attempt): ?int
    {
        if (! $attempt->started_at || ! $attempt->completed_at) {
            return null;
        }

        // $absolute must be explicit: this Carbon version's diffInSeconds()
        // defaults to a SIGNED difference, which silently inverted the
        // lower-time-wins tie-break below (a negative "duration" made a
        // slower attempt look shorter than a faster one).
        return (int) $attempt->completed_at->diffInSeconds($attempt->started_at, absolute: true);
    }

    private static function formatRow(QuizAttempt $attempt, int $rank): array
    {
        return [
            'rank' => $rank,
            'user_id' => $attempt->user_id,
            'name' => $attempt->user->name ?? 'Unknown',
            'avatar' => $attempt->user->avatar ?? null,
            'score' => $attempt->score,
            'correct_answers' => $attempt->correct_answers,
            'wrong_answers' => $attempt->wrong_answers,
            'accuracy' => $attempt->accuracy,
            'time_taken_seconds' => self::timeTakenSeconds($attempt),
        ];
    }
}
