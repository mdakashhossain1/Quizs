<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Quizs Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ── Nav styles ── */
        .nav-link { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; color: #9ca3af; text-decoration: none; transition: background 0.15s, color 0.15s, transform 0.1s; }
        .nav-link:hover { background: #374151; color: #fff; transform: translateX(2px); }
        .nav-link.active { background: #2563eb; color: #fff; }
        .nav-link svg { width: 18px; height: 18px; flex-shrink: 0; }
        .nav-section { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280; padding: 16px 12px 6px; }

        /* ── Shimmer / Skeleton ── */
        @keyframes shimmer {
            0%   { background-position: -600px 0; }
            100% { background-position:  600px 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 600px 100%;
            animation: shimmer 1.4s infinite linear;
            border-radius: 6px;
        }

        /* ── Pulse (for stat cards while loading) ── */
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.45; } }
        .animate-pulse { animation: pulse 1.8s ease-in-out infinite; }

        /* ── Page entrance fade-in-up ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0);    }
        }
        .animate-fade-in  { animation: fadeInUp 0.35s ease-out both; }
        .animate-fade-in-1 { animation: fadeInUp 0.35s 0.05s ease-out both; }
        .animate-fade-in-2 { animation: fadeInUp 0.35s 0.10s ease-out both; }
        .animate-fade-in-3 { animation: fadeInUp 0.35s 0.15s ease-out both; }
        .animate-fade-in-4 { animation: fadeInUp 0.35s 0.20s ease-out both; }

        /* ── Button micro-interaction ── */
        button, a.btn { transition: transform 0.1s, box-shadow 0.1s; }
        button:active, a.btn:active { transform: scale(0.97); }

        /* ── Table row hover ── */
        tbody tr { transition: background 0.12s; }
        tbody tr:hover { background: #f5f3ff !important; }

        /* ── Card hover lift ── */
        .card-hover { transition: box-shadow 0.2s, transform 0.2s; }
        .card-hover:hover { box-shadow: 0 8px 24px rgba(105,0,197,0.12); transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="flex h-screen overflow-hidden">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="w-60 bg-gray-900 flex flex-col flex-shrink-0">

        {{-- Brand --}}
        <div class="flex flex-col items-center px-4 py-4 border-b border-gray-700">
            <img src="{{ asset('logo_icon.png') }}" alt="Quizs Logo" class="h-10 w-10 object-cover rounded-xl">
            <p class="text-gray-400 text-xs mt-1">Management Panel</p>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            <div class="nav-section">Main</div>

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>

            <a href="{{ route('admin.quizzes.index') }}"
               class="nav-link {{ request()->routeIs('admin.quizzes.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Quizzes
            </a>

            <a href="{{ route('admin.questions.index') }}"
               class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Questions Bank
            </a>

            <a href="{{ route('admin.quiz-attempts.index') }}"
               class="nav-link {{ request()->routeIs('admin.quiz-attempts.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Quiz Attempts
            </a>

            <div class="nav-section">Administration</div>

            <a href="{{ route('admin.users.index') }}"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users
            </a>

            <a href="{{ route('admin.targets.index') }}"
               class="nav-link {{ request()->routeIs('admin.targets.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Daily Targets
            </a>

            <a href="{{ route('admin.attendance.index') }}"
               class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Attendance
            </a>

            <a href="{{ route('admin.push-notifications.index') }}"
               class="nav-link {{ request()->routeIs('admin.push-notifications.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Push Notifications
            </a>

            <a href="{{ route('admin.settings.index') }}"
               class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </nav>

        {{-- Footer user info --}}
        <div class="px-4 py-3 border-t border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                    <p class="text-gray-400 text-xs truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" title="Logout" class="text-gray-400 hover:text-red-400 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        {{-- Top Header --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div>
                <h1 class="text-gray-900 font-semibold text-base">@yield('title', 'Dashboard')</h1>
                <p class="text-gray-500 text-xs">@yield('breadcrumb', 'Quizs Admin Panel')</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 text-xs text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    System Online
                </span>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-6">

            @if(session('success'))
                <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded">
                    <svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded">
                    <svg class="w-4 h-4 flex-shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded">
                    <p class="font-semibold mb-1">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
