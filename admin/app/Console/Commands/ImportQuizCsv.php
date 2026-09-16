<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportQuizCsv extends Command
{
    protected $signature = 'quiz:import';

    protected $description = 'One-time bootstrap: import the bundled CSV question banks into the quiz/question/option tables managed by the admin panel.';

    /**
     * @var list<array{file: string, category_slug: string, language: string}>
     */
    private const FILES = [
        ['file' => 'questions_math_english.csv', 'category_slug' => 'mathematics-logic', 'language' => 'en'],
        ['file' => 'questions_math_hindi.csv', 'category_slug' => 'mathematics-logic', 'language' => 'hi'],
        ['file' => 'questions_science_english.csv', 'category_slug' => 'science-nature', 'language' => 'en'],
        ['file' => 'questions_science_hindi.csv', 'category_slug' => 'science-nature', 'language' => 'hi'],
        ['file' => 'questions_gk_english.csv', 'category_slug' => 'general-knowledge', 'language' => 'en'],
        ['file' => 'questions_gk_hindi.csv', 'category_slug' => 'general-knowledge', 'language' => 'hi'],
    ];

    public function handle(): int
    {
        $totalQuizzes = 0;
        $totalQuestions = 0;

        foreach (self::FILES as $entry) {
            $path = base_path('../assets/questions/'.$entry['file']);

            if (! file_exists($path)) {
                $this->error("Missing CSV: {$path}");

                continue;
            }

            $category = Category::where('slug', $entry['category_slug'])->first();

            if (! $category) {
                $this->error("Category not found: {$entry['category_slug']}");

                continue;
            }

            $rowsBySubtopic = $this->groupRowsBySubtopic($path);

            DB::transaction(function () use ($category, $entry, $rowsBySubtopic, &$totalQuizzes, &$totalQuestions): void {
                // Re-runnable: wipe this category+language's existing quizzes first
                // (cascades to their questions/options via the existing FK constraints).
                Quiz::where('category_id', $category->id)
                    ->where('language', $entry['language'])
                    ->get()
                    ->each(fn (Quiz $quiz) => $quiz->delete());

                foreach ($rowsBySubtopic as $subtopic => $rows) {
                    $quiz = Quiz::create([
                        'category_id' => $category->id,
                        'language' => $entry['language'],
                        'title' => $subtopic,
                        'slug' => Str::slug("{$category->slug}-{$subtopic}-{$entry['language']}"),
                        'difficulty' => 'medium',
                        'passing_percentage' => 60,
                        'duration_minutes' => max(10, (int) ceil(count($rows) * 0.75)),
                        'is_active' => true,
                    ]);
                    $totalQuizzes++;

                    foreach (array_values($rows) as $index => $row) {
                        $question = Question::create([
                            'quiz_id' => $quiz->id,
                            'question_text' => $row['question'],
                            'meaning' => $row['meaning'],
                            'explanation' => $row['explanation'],
                            'points' => 10,
                            'sort_order' => $index,
                        ]);
                        $totalQuestions++;

                        $correctLetter = strtoupper(trim($row['correct_option']));
                        foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $column) {
                            Option::create([
                                'question_id' => $question->id,
                                'option_text' => $row[$column],
                                'is_correct' => $letter === $correctLetter,
                            ]);
                        }
                    }
                }
            });

            $this->info("Imported {$entry['file']} ({$entry['language']}) into '{$category->name}': ".count($rowsBySubtopic).' quizzes.');
        }

        $this->info("Done. Total quizzes: {$totalQuizzes}, total questions: {$totalQuestions}.");

        return self::SUCCESS;
    }

    /**
     * Reads a CSV and groups its rows by subtopic, skipping placeholder
     * "sample" rows — matching the Flutter app's existing CSV grouping filter.
     *
     * @return array<string, array<int, array<string, string>>>
     */
    private function groupRowsBySubtopic(string $path): array
    {
        $grouped = [];
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header[0] = preg_replace('/^\x{FEFF}/u', '', (string) $header[0]);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);
            $subtopic = trim($data['subtopic']);

            if ($subtopic === '' || str_contains(strtolower($subtopic), 'sample')) {
                continue;
            }

            $grouped[$subtopic][] = $data;
        }

        fclose($handle);

        return $grouped;
    }
}
