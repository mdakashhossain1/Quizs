<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Quizs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 relative overflow-hidden">

    <!-- Background glow shapes -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-purple-600/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-indigo-600/20 blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-500 flex items-center justify-center text-white font-extrabold text-2xl shadow-lg shadow-purple-600/30 mb-3">
                Q
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Quizs Admin Panel</h1>
            <p class="text-xs text-slate-400 mt-1">Sign in with your administrative credentials</p>
        </div>

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email', 'admin@quizs.com') }}" required autofocus
                    class="w-full px-4 py-2.5 bg-slate-800/80 border @error('email') border-rose-500 @else border-slate-700/80 @enderror rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
                @error('email')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="password" name="password" value="password123" required
                    class="w-full px-4 py-2.5 bg-slate-800/80 border @error('password') border-rose-500 @else border-slate-700/80 @enderror rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
                @error('password')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-700 bg-slate-800">
                    <span class="text-xs text-slate-400">Remember me</span>
                </label>
                <span class="text-xs text-slate-500">Default: password123</span>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold rounded-xl text-sm shadow-lg shadow-purple-600/30 hover:shadow-purple-600/50 transition-all cursor-pointer">
                Sign In to Dashboard
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center">
            <p class="text-[11px] text-slate-500">Quizs Platform &copy; {{ date('Y') }} &bull; Normal Laravel MVC Backend</p>
        </div>
    </div>

</body>
</html>
