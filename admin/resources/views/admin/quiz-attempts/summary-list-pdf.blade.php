<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quiz Attempts Summary</title>
    <style>
        /* Registered in PHP via FontMetrics::registerFont — see
           QuizAttemptController::registerDevanagariFont. */
        body { font-family: 'NotoDevanagari', sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .subtitle { color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; }
        th { background: #f3f4f6; }
        td.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Quiz Attempts — Result Summary</h1>
    <p class="subtitle">{{ $attempts->count() }} attempts — numbers only, no question-by-question detail.</p>

    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Quiz</th>
            <th>Status</th>
            <th>Played At</th>
            <th>Total</th>
            <th>Correct</th>
            <th>Wrong</th>
            <th>Unanswered</th>
            <th>Score</th>
            <th>Accuracy</th>
        </tr>
        @foreach($attempts as $attempt)
            <tr>
                <td>{{ $attempt->id }}</td>
                <td>{{ $attempt->user->name ?? 'Unknown' }}<br><span style="color:#9ca3af">{{ $attempt->user->email ?? '' }}</span></td>
                <td>{{ $attempt->quiz->title ?? 'Deleted Quiz' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $attempt->status)) }}</td>
                <td>{{ $attempt->completed_at?->format('M d, Y H:i') ?? $attempt->started_at?->format('M d, Y H:i') ?? '-' }}</td>
                <td class="num">{{ $attempt->total_questions }}</td>
                <td class="num">{{ $attempt->correct_answers }}</td>
                <td class="num">{{ $attempt->wrong_answers }}</td>
                <td class="num">{{ $attempt->unanswered_questions }}</td>
                <td class="num">{{ $attempt->score }}</td>
                <td class="num">{{ $attempt->accuracy !== null ? number_format($attempt->accuracy, 0).'%' : '-' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
