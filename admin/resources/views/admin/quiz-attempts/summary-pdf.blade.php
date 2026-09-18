<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Result Summary</title>
    <style>
        /* Registered in PHP via FontMetrics::registerFont — see
           QuizAttemptController::registerDevanagariFont. Noto Sans
           Devanagari also covers basic Latin, so one font handles both
           English labels and Hindi quiz titles/names. */
        body { font-family: 'NotoDevanagari', sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #6b7280; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; }
        .id-table td { padding: 4px 0; }
        .id-table td:first-child { color: #6b7280; width: 160px; }
        .summary-table { margin-top: 20px; border: 1px solid #d1d5db; }
        .summary-table th, .summary-table td { border: 1px solid #d1d5db; padding: 8px 12px; text-align: left; }
        .summary-table th { background: #f3f4f6; }
        .summary-table td.value { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Quiz Result Summary</h1>
    <p class="subtitle">Numerical summary only — no question-by-question detail.</p>

    <table class="id-table">
        <tr><td>User</td><td>{{ $quizAttempt->user->name ?? 'Unknown' }} ({{ $quizAttempt->user->email ?? '-' }})</td></tr>
        <tr><td>Quiz</td><td>{{ $quizAttempt->quiz->title ?? 'Deleted Quiz' }}</td></tr>
        <tr><td>Attempt ID</td><td>#{{ $quizAttempt->id }}</td></tr>
        <tr><td>Status</td><td>{{ ucfirst(str_replace('_', ' ', $quizAttempt->status)) }}</td></tr>
        <tr><td>Played At</td><td>{{ $quizAttempt->completed_at?->format('M d, Y H:i') ?? $quizAttempt->started_at?->format('M d, Y H:i') ?? '-' }}</td></tr>
    </table>

    <table class="summary-table">
        <tr><th>Field</th><th style="text-align:right">Value</th></tr>
        <tr><td>Total Questions</td><td class="value">{{ $quizAttempt->total_questions }}</td></tr>
        <tr><td>Correct Answers</td><td class="value">{{ $quizAttempt->correct_answers }}</td></tr>
        <tr><td>Wrong Answers</td><td class="value">{{ $quizAttempt->wrong_answers }}</td></tr>
        <tr><td>Unanswered</td><td class="value">{{ $quizAttempt->unanswered_questions }}</td></tr>
        <tr><td>Score</td><td class="value">{{ $quizAttempt->score }} pts</td></tr>
        <tr><td>Accuracy</td><td class="value">{{ $quizAttempt->accuracy !== null ? number_format($quizAttempt->accuracy, 0).'%' : '-' }}</td></tr>
    </table>
</body>
</html>
