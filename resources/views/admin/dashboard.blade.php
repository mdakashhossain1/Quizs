@extends('layouts.admin')

@section('title', 'Analytics Dashboard')
@section('breadcrumb', 'Visual performance analytics, questions answered & player activity')

@section('content')

{{-- Time-Range Filter & Export Action Bar --}}
<div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 mb-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0" id="range-btn-group">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1 hidden sm:inline">Range:</span>
        <button type="button" onclick="loadDashboardAnalytics('today')" class="range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all {{ $range === 'today' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}" data-range="today">
            Today
        </button>
        <button type="button" onclick="loadDashboardAnalytics('yesterday')" class="range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all {{ $range === 'yesterday' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}" data-range="yesterday">
            Yesterday
        </button>
        <button type="button" onclick="loadDashboardAnalytics('7days')" class="range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all {{ $range === '7days' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}" data-range="7days">
            Last 7 Days
        </button>
        <button type="button" onclick="loadDashboardAnalytics('30days')" class="range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all {{ $range === '30days' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}" data-range="30days">
            Last 30 Days
        </button>
        <button type="button" onclick="loadDashboardAnalytics('year')" class="range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all {{ $range === 'year' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}" data-range="year">
            This Year
        </button>
    </div>

    <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
        <div id="analytics-spinner" class="hidden text-xs text-blue-600 font-medium flex items-center gap-1.5">
            <svg class="animate-spin h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Updating...</span>
        </div>

        <a id="export-csv-btn" href="{{ route('admin.dashboard.export', ['range' => $range]) }}" class="inline-flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-700 text-xs font-semibold px-3.5 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV Report
        </a>
    </div>
</div>

{{-- Top KPI Stat Cards with Trends --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">

    {{-- Questions Answered --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Questions Answered</p>
            <div class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p id="stat-q-today" class="text-2xl font-bold text-gray-900">{{ number_format($stats['questions_answered_today']) }}</p>
        <div class="flex items-center gap-1.5 mt-1.5">
            <span id="stat-q-badge" class="inline-flex items-center text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $stats['questions_growth_pct']['direction'] === 'up' ? 'bg-emerald-50 text-emerald-700' : ($stats['questions_growth_pct']['direction'] === 'down' ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-600') }}">
                {{ $stats['questions_growth_pct']['text'] }}
            </span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Total: <span id="stat-q-total">{{ number_format($stats['total_questions_answered']) }}</span> answered</p>
    </div>

    {{-- Quiz Plays & Attempts --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quiz Attempts</p>
            <div class="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p id="stat-att-today" class="text-2xl font-bold text-gray-900">{{ number_format($stats['attempts_today']) }}</p>
        <div class="flex items-center gap-1.5 mt-1.5">
            <span id="stat-att-badge" class="inline-flex items-center text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $stats['attempts_growth_pct']['direction'] === 'up' ? 'bg-emerald-50 text-emerald-700' : ($stats['attempts_growth_pct']['direction'] === 'down' ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-600') }}">
                {{ $stats['attempts_growth_pct']['text'] }}
            </span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Total: <span id="stat-att-total">{{ number_format($stats['total_attempts']) }}</span> plays</p>
    </div>

    {{-- Platform Accuracy --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Accuracy Rate</p>
            <div class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p id="stat-acc-today" class="text-2xl font-bold text-emerald-600">{{ $stats['accuracy_today'] }}%</p>
        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2 overflow-hidden">
            <div id="stat-acc-bar" class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $stats['accuracy_today'])) }}%"></div>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">Overall: <span id="stat-acc-overall">{{ $stats['overall_accuracy'] }}%</span> correct</p>
    </div>

    {{-- Online & Total Players --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Online Now</p>
            <div class="w-7 h-7 bg-green-50 rounded-lg flex items-center justify-center">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['online_users']) }}</p>
        <div class="flex items-center gap-1.5 mt-1.5">
            <span class="inline-flex items-center text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $stats['users_growth_pct']['direction'] === 'up' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $stats['users_growth_pct']['text'] }}
            </span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Total: <span id="stat-users-total">{{ number_format($stats['total_users']) }}</span> users</p>
    </div>

    {{-- Daily Targets Completed --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Targets Achieved</p>
            <div class="w-7 h-7 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
        <p id="stat-targets-today" class="text-2xl font-bold text-purple-700">{{ number_format($stats['targets_completed_today']) }}</p>
        <p class="text-xs text-purple-500 mt-1">Goal Achievers Today</p>
        <p class="text-[11px] text-gray-400 mt-1.5">Daily target engagement</p>
    </div>

    {{-- Available Question Bank --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Question Bank</p>
            <div class="w-7 h-7 bg-indigo-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_questions']) }}</p>
        <p class="text-xs text-indigo-600 font-medium mt-1">{{ $stats['total_quizzes'] }} Quizzes</p>
        <p class="text-[11px] text-gray-400 mt-1.5">{{ $stats['total_categories'] }} Subject Categories</p>
    </div>

</div>

{{-- Interactive Charts Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Chart 1: Questions Answered & Accuracy Performance (Col span 2) --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 border-b border-gray-100 gap-2">
            <div>
                <h3 class="font-bold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                    <span>Questions Answered & Accuracy Performance</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Timeline of answered questions, correct responses, and errors</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Correct</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Wrong</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Total Answered</span>
            </div>
        </div>
        <div class="mt-4">
            <div id="questions-chart" style="min-height: 320px;"></div>
        </div>
    </div>

    {{-- Chart 2: Category Breakdown & Engagement (Col span 1) --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm sm:text-base">Subject Distribution</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Questions & plays by category</p>
                </div>
            </div>
            <div class="mt-4">
                <div id="category-chart" style="min-height: 280px;"></div>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <span>Core Subjects: Science, Math, GK</span>
            <span class="font-semibold text-gray-700">100% Bilingual (EN/HI)</span>
        </div>
    </div>

    {{-- Chart 3: Quiz Attempts & Completion Trend (Col span 2) --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 border-b border-gray-100 gap-2">
            <div>
                <h3 class="font-bold text-gray-900 text-sm sm:text-base">Quiz Plays & Completion Activity</h3>
                <p class="text-xs text-gray-500 mt-0.5">Total quiz sessions started vs fully completed plays</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-600"></span> Completed Plays</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-300"></span> Total Started</span>
            </div>
        </div>
        <div class="mt-4">
            <div id="attempts-chart" style="min-height: 290px;"></div>
        </div>
    </div>

    {{-- Chart 4: Daily Target Achievement (Col span 1) --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm sm:text-base">Daily Target Status</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Player daily goal completion today</p>
                </div>
            </div>
            <div class="mt-4">
                <div id="targets-chart" style="min-height: 250px;"></div>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span> Completed</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> In Progress</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span> Pending</span>
        </div>
    </div>

</div>

{{-- Quick Actions --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.quizzes.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium px-4 py-2 rounded-lg transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Quiz
    </a>
    <a href="{{ route('admin.questions.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-xs sm:text-sm font-medium px-4 py-2 rounded-lg transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Question
    </a>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-xs sm:text-sm font-medium px-4 py-2 rounded-lg transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Category
    </a>
</div>

{{-- Tables Row: Recent Quiz Attempts & New Players --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Attempts --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Recent Quiz Attempts</h3>
                <p class="text-xs text-gray-400">Latest completed attempts from app players</p>
            </div>
            <a href="{{ route('admin.quiz-attempts.index') }}" class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1">
                View all &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">User</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">Quiz</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">Score</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">Accuracy</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentAttempts as $att)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $att->user->name ?? 'Player' }}</td>
                            <td class="px-4 py-3 text-gray-600 truncate max-w-[200px]">{{ $att->quiz->title ?? 'Quiz' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">+{{ $att->score }} pts</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <span class="font-medium text-emerald-600">{{ $att->correct_answers }}</span> / {{ $att->total_questions }}
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $att->completed_at ? $att->completed_at->diffForHumans() : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No recent quiz attempts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">New Players</h3>
                <p class="text-xs text-gray-400">Recently registered app users</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1">
                View all &rarr;
            </a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentUsers as $u)
                <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50/70 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-900">{{ $u->name }}</p>
                            <p class="text-[11px] text-gray-400">{{ $u->email }}</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-gray-400">{{ $u->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-400 text-sm">No registered users yet.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Initial Data from Server
    let chartData = @json($charts);
    let currentRange = '{{ $range }}';

    // ── Chart 1: Questions Answered & Accuracy Performance ──
    const questionsChartOptions = {
        series: [
            { name: 'Total Answered', data: chartData.timeline.questions_answered },
            { name: 'Correct Answers', data: chartData.timeline.questions_correct },
            { name: 'Wrong Answers', data: chartData.timeline.questions_wrong }
        ],
        chart: {
            type: 'area',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: true,
                tools: { download: true, selection: false, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true }
            },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        colors: ['#3b82f6', '#10b981', '#ef4444'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 95, 100] }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: [2.5, 2.5, 2] },
        xaxis: {
            categories: chartData.timeline.labels,
            labels: { style: { colors: '#6b7280', fontSize: '11px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: '#6b7280', fontSize: '11px' },
                formatter: (val) => Math.round(val)
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: (val) => val + ' questions' }
        },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 3 }
    };
    const questionsChart = new ApexCharts(document.querySelector("#questions-chart"), questionsChartOptions);
    questionsChart.render();

    // ── Chart 2: Category Breakdown (Donut) ──
    const categoryChartOptions = {
        series: chartData.categories.questions.length ? chartData.categories.questions : [1],
        labels: chartData.categories.labels.length ? chartData.categories.labels : ['No Categories'],
        chart: {
            type: 'donut',
            height: 280,
            fontFamily: 'Inter, sans-serif'
        },
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'],
        legend: { position: 'bottom', fontSize: '11px' },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Questions',
                            formatter: () => chartData.categories.questions.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: { formatter: (val) => val + ' questions' }
        }
    };
    const categoryChart = new ApexCharts(document.querySelector("#category-chart"), categoryChartOptions);
    categoryChart.render();

    // ── Chart 3: Quiz Attempts & Activity Trend (Area) ──
    const attemptsChartOptions = {
        series: [
            { name: 'Completed Plays', data: chartData.timeline.completed_attempts },
            { name: 'Total Started', data: chartData.timeline.attempts }
        ],
        chart: {
            type: 'area',
            height: 290,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        colors: ['#6366f1', '#cbd5e1'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 95, 100] }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            categories: chartData.timeline.labels,
            labels: { style: { colors: '#6b7280', fontSize: '11px' } }
        },
        yaxis: {
            labels: {
                style: { colors: '#6b7280', fontSize: '11px' },
                formatter: (val) => Math.round(val)
            }
        },
        tooltip: {
            shared: true,
            y: { formatter: (val) => val + ' plays' }
        },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 3 }
    };
    const attemptsChart = new ApexCharts(document.querySelector("#attempts-chart"), attemptsChartOptions);
    attemptsChart.render();

    // ── Chart 4: Daily Target Progress Status (Bar) ──
    const targetsChartOptions = {
        series: [{
            name: 'Players',
            data: [
                chartData.targets.completed,
                chartData.targets.in_progress,
                chartData.targets.not_started
            ]
        }],
        chart: {
            type: 'bar',
            height: 250,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '55%',
                distributed: true
            }
        },
        colors: ['#9333ea', '#fbbf24', '#cbd5e1'],
        dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 'bold' } },
        xaxis: {
            categories: ['Completed', 'In Progress', 'Pending'],
            labels: { style: { fontSize: '11px', colors: '#6b7280' } }
        },
        yaxis: { show: false },
        legend: { show: false },
        tooltip: {
            y: { formatter: (val) => val + ' players' }
        },
        grid: { show: false }
    };
    const targetsChart = new ApexCharts(document.querySelector("#targets-chart"), targetsChartOptions);
    targetsChart.render();

    // ── Interactive Range Switching (AJAX) ──
    async function loadDashboardAnalytics(range) {
        if (range === currentRange) return;
        currentRange = range;

        // Update button states
        document.querySelectorAll('.range-btn').forEach(btn => {
            if (btn.dataset.range === range) {
                btn.className = 'range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all bg-blue-600 text-white shadow-sm';
            } else {
                btn.className = 'range-btn text-xs font-medium px-3.5 py-2 rounded-lg transition-all bg-gray-100 text-gray-600 hover:bg-gray-200';
            }
        });

        // Show spinner
        const spinner = document.getElementById('analytics-spinner');
        spinner.classList.remove('hidden');

        try {
            const res = await fetch(`{{ route('admin.dashboard.analytics') }}?range=${range}`);
            const data = await res.json();

            if (data.success) {
                chartData = data.charts;

                // Update Chart 1: Questions
                questionsChart.updateOptions({
                    xaxis: { categories: chartData.timeline.labels }
                });
                questionsChart.updateSeries([
                    { name: 'Total Answered', data: chartData.timeline.questions_answered },
                    { name: 'Correct Answers', data: chartData.timeline.questions_correct },
                    { name: 'Wrong Answers', data: chartData.timeline.questions_wrong }
                ]);

                // Update Chart 3: Attempts
                attemptsChart.updateOptions({
                    xaxis: { categories: chartData.timeline.labels }
                });
                attemptsChart.updateSeries([
                    { name: 'Completed Plays', data: chartData.timeline.completed_attempts },
                    { name: 'Total Started', data: chartData.timeline.attempts }
                ]);

                // Update Chart 4: Targets
                targetsChart.updateSeries([{
                    name: 'Players',
                    data: [
                        chartData.targets.completed,
                        chartData.targets.in_progress,
                        chartData.targets.not_started
                    ]
                }]);

                // Update Export CSV Link
                document.getElementById('export-csv-btn').href = `{{ route('admin.dashboard.export') }}?range=${range}`;
            }
        } catch (e) {
            console.error('Error fetching analytics:', e);
        } finally {
            spinner.classList.add('hidden');
        }
    }
</script>
@endpush
