<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@quizs.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'streak' => 12,
                'score' => 1200,
                'is_active' => true,
            ]
        );

        // 2. Mobile / Test User
        $user = User::firstOrCreate(
            ['email' => 'user@quizs.com'],
            [
                'name' => 'Alex Johnson',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'streak' => 4,
                'score' => 350,
                'is_active' => true,
            ]
        );

        // 3. Categories
        $categories = [
            [
                'name' => 'Science & Nature',
                'slug' => 'science-nature',
                'icon' => 'heroicon-o-beaker',
                'color' => '#3B82F6',
                'description' => 'Explore physics, chemistry, biology, and the wonders of nature.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Mathematics & Logic',
                'slug' => 'mathematics-logic',
                'icon' => 'heroicon-o-calculator',
                'color' => '#10B981',
                'description' => 'Test your arithmetic, algebra, puzzles, and reasoning skills.',
                'sort_order' => 2,
            ],
            [
                'name' => 'General Knowledge',
                'slug' => 'general-knowledge',
                'icon' => 'heroicon-o-globe-alt',
                'color' => '#F59E0B',
                'description' => 'Trivia from world events, cultures, landmarks, and discoveries.',
                'sort_order' => 3,
            ],
            [
                'name' => 'History & Heritage',
                'slug' => 'history-heritage',
                'icon' => 'heroicon-o-book-open',
                'color' => '#8B5CF6',
                'description' => 'Journey through ancient civilizations, wars, and world heritage.',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $catData) {
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            // Seed quizzes under category
            if ($category->slug === 'science-nature') {
                $quiz = \App\Models\Quiz::firstOrCreate(
                    ['slug' => 'solar-system-wonders'],
                    [
                        'category_id' => $category->id,
                        'title' => 'Solar System Wonders',
                        'description' => 'How well do you know the planets and stars in our solar system?',
                        'difficulty' => 'easy',
                        'duration_minutes' => 10,
                        'passing_percentage' => 60,
                        'sort_order' => 1,
                        'is_active' => true,
                    ]
                );

                $q1 = \App\Models\Question::firstOrCreate(
                    ['quiz_id' => $quiz->id, 'sort_order' => 1],
                    [
                        'question_text' => 'Which planet is known as the Red Planet?',
                        'explanation' => 'Mars appears red because of iron oxide (rust) on its surface.',
                        'points' => 10,
                    ]
                );
                $q1->options()->firstOrCreate(['option_text' => 'Venus', 'is_correct' => false]);
                $q1->options()->firstOrCreate(['option_text' => 'Mars', 'is_correct' => true]);
                $q1->options()->firstOrCreate(['option_text' => 'Jupiter', 'is_correct' => false]);
                $q1->options()->firstOrCreate(['option_text' => 'Saturn', 'is_correct' => false]);

                $q2 = \App\Models\Question::firstOrCreate(
                    ['quiz_id' => $quiz->id, 'sort_order' => 2],
                    [
                        'question_text' => 'What is the largest planet in our solar system?',
                        'explanation' => 'Jupiter is more than twice as massive as all the other planets combined.',
                        'points' => 10,
                    ]
                );
                $q2->options()->firstOrCreate(['option_text' => 'Earth', 'is_correct' => false]);
                $q2->options()->firstOrCreate(['option_text' => 'Saturn', 'is_correct' => false]);
                $q2->options()->firstOrCreate(['option_text' => 'Jupiter', 'is_correct' => true]);
                $q2->options()->firstOrCreate(['option_text' => 'Neptune', 'is_correct' => false]);

                $q3 = \App\Models\Question::firstOrCreate(
                    ['quiz_id' => $quiz->id, 'sort_order' => 3],
                    [
                        'question_text' => 'Which is the closest star to Earth?',
                        'explanation' => 'The Sun is the star at the center of the Solar System.',
                        'points' => 10,
                    ]
                );
                $q3->options()->firstOrCreate(['option_text' => 'Proxima Centauri', 'is_correct' => false]);
                $q3->options()->firstOrCreate(['option_text' => 'The Sun', 'is_correct' => true]);
                $q3->options()->firstOrCreate(['option_text' => 'Sirius', 'is_correct' => false]);
                $q3->options()->firstOrCreate(['option_text' => 'Betelgeuse', 'is_correct' => false]);
            }

            if ($category->slug === 'general-knowledge') {
                $quiz = \App\Models\Quiz::firstOrCreate(
                    ['slug' => 'world-geography-trivia'],
                    [
                        'category_id' => $category->id,
                        'title' => 'World Geography Trivia',
                        'description' => 'Test your global geography awareness with capitals and landmarks.',
                        'difficulty' => 'medium',
                        'duration_minutes' => 15,
                        'passing_percentage' => 70,
                        'sort_order' => 1,
                        'is_active' => true,
                    ]
                );

                $q1 = \App\Models\Question::firstOrCreate(
                    ['quiz_id' => $quiz->id, 'sort_order' => 1],
                    [
                        'question_text' => 'What is the capital city of Australia?',
                        'explanation' => 'Canberra was chosen as the capital city as a compromise between Sydney and Melbourne.',
                        'points' => 10,
                    ]
                );
                $q1->options()->firstOrCreate(['option_text' => 'Sydney', 'is_correct' => false]);
                $q1->options()->firstOrCreate(['option_text' => 'Melbourne', 'is_correct' => false]);
                $q1->options()->firstOrCreate(['option_text' => 'Canberra', 'is_correct' => true]);
                $q1->options()->firstOrCreate(['option_text' => 'Brisbane', 'is_correct' => false]);
            }
        }

        // 4. Sample Attempt
        $firstQuiz = \App\Models\Quiz::first();
        if ($firstQuiz && $user) {
            \App\Models\QuizAttempt::firstOrCreate(
                ['user_id' => $user->id, 'quiz_id' => $firstQuiz->id],
                [
                    'score' => 20,
                    'total_questions' => 3,
                    'correct_answers' => 2,
                    'completed_at' => now(),
                ]
            );
        }
    }
}
