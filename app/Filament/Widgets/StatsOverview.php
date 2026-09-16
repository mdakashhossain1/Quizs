<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Categories & Quizzes', Category::count() . ' / ' . Quiz::count())
                ->description('Active quiz topics')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Questions in Bank', Question::count())
                ->description('Multiple-choice items')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Total Quiz Attempts', QuizAttempt::count())
                ->description('Completed user games')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('info'),
        ];
    }
}
