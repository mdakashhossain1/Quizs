<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · Quizs</title>
    <style>
        :root {
            --purple: #53009C;
            --dark-purple: #400078;
            --ink: #1E1E1E;
            --muted: #5B5560;
            --bg: #F6F2FB;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.6;
        }
        header {
            background: linear-gradient(135deg, var(--purple), var(--dark-purple));
            color: #fff;
            padding: 28px 20px 24px;
        }
        header .brand {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }
        header h1 {
            margin: 10px 0 2px;
            font-size: 24px;
        }
        header .updated {
            font-size: 13px;
            opacity: 0.85;
        }
        main {
            max-width: 680px;
            margin: 0 auto;
            padding: 28px 20px 60px;
        }
        section { margin-bottom: 26px; }
        h2 {
            font-size: 16px;
            color: var(--purple);
            margin: 0 0 8px;
        }
        p, li { color: var(--ink); font-size: 14.5px; }
        p { margin: 0 0 10px; }
        ul { margin: 0 0 10px; padding-left: 20px; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 20px 22px;
            box-shadow: 0 2px 10px rgba(83, 0, 156, 0.06);
        }
        footer {
            text-align: center;
            font-size: 12.5px;
            color: var(--muted);
            padding: 20px;
        }
        a { color: var(--purple); }
    </style>
</head>
<body>
    <header>
        <div class="brand">Quizs</div>
        <h1>@yield('title')</h1>
        <div class="updated">Last updated: @yield('updated', 'September 2026')</div>
    </header>
    <main>
        <div class="card">
            @yield('content')
        </div>
    </main>
    <footer>
        &copy; {{ date('Y') }} Quizs. Questions? Contact us at
        <a href="mailto:support@quizs.in">support@quizs.in</a>.
    </footer>
</body>
</html>
