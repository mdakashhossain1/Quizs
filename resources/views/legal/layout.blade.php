<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · Quizs Legal Portal</title>
    <meta name="description" content="Official legal documentation, Privacy Policy, and Terms and Conditions for Quizs bilingual quiz mobile application.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #53009C;
            --purple-light: #6A00C8;
            --purple-subtle: #F3E8FF;
            --dark-purple: #350064;
            --ink-primary: #111827;
            --ink-secondary: #374151;
            --ink-muted: #6B7280;
            --bg-page: #F8F9FD;
            --bg-card: #FFFFFF;
            --border-color: #E5E7EB;
            --border-highlight: #DDD6FE;
            --accent-green: #059669;
            --accent-blue: #2563EB;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px rgba(83, 0, 156, 0.08);
            --shadow-lg: 0 10px 30px rgba(83, 0, 156, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-page);
            color: var(--ink-secondary);
            line-height: 1.75;
            font-size: 15.5px;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Bar & Header */
        .site-header {
            background: linear-gradient(135deg, var(--dark-purple) 0%, var(--purple) 60%, var(--purple-light) 100%);
            color: #ffffff;
            padding: 42px 24px 38px;
            position: relative;
            box-shadow: 0 4px 20px rgba(53, 0, 100, 0.25);
        }
        .header-inner {
            max-width: 1060px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 16px;
        }
        .brand-link {
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: -0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .brand-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .nav-links {
            display: flex;
            gap: 12px;
        }
        .nav-links a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
        }
        .header-title-block h1 {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.8px;
            line-height: 1.25;
            margin-top: 4px;
            color: #ffffff;
        }
        .header-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.82);
            margin-top: 6px;
        }
        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.15);
            padding: 4px 10px;
            border-radius: 6px;
        }

        /* Layout Grid */
        .container {
            max-width: 1060px;
            margin: -24px auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 32px;
            align-items: start;
            position: relative;
        }
        @media (max-width: 880px) {
            .container {
                grid-template-columns: 1fr;
                margin-top: 20px;
            }
        }

        /* Sidebar Navigation */
        .sidebar {
            position: sticky;
            top: 24px;
            background: var(--bg-card);
            border-radius: 14px;
            padding: 20px 18px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            max-height: calc(100vh - 48px);
            overflow-y: auto;
        }
        @media (max-width: 880px) {
            .sidebar {
                display: none;
            }
        }
        .sidebar-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--ink-muted);
            letter-spacing: 0.8px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .sidebar-menu a {
            color: var(--ink-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 6px;
            display: block;
            line-height: 1.4;
            transition: all 0.15s ease;
        }
        .sidebar-menu a:hover {
            background: var(--purple-subtle);
            color: var(--purple);
            font-weight: 600;
            padding-left: 14px;
        }

        /* Main Legal Content Card */
        .legal-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 44px 48px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }
        @media (max-width: 640px) {
            .legal-card {
                padding: 24px 20px;
            }
            .header-title-block h1 {
                font-size: 24px;
            }
        }

        /* Typography & Legal Formatting */
        .legal-card h2 {
            font-size: 21px;
            font-weight: 800;
            color: var(--dark-purple);
            margin: 38px 0 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-highlight);
            letter-spacing: -0.4px;
            scroll-margin-top: 30px;
            display: flex;
            align-items: baseline;
            gap: 10px;
        }
        .legal-card h2:first-of-type {
            margin-top: 10px;
        }
        .legal-card h2 .sec-num {
            color: var(--purple);
            font-size: 17px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
        }
        .legal-card h3 {
            font-size: 16.5px;
            font-weight: 700;
            color: var(--ink-primary);
            margin: 22px 0 10px;
        }
        .legal-card h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--purple);
            margin: 16px 0 8px;
        }
        .legal-card p {
            margin-bottom: 16px;
            color: var(--ink-secondary);
            font-size: 15px;
            text-align: justify;
        }
        .legal-card ul, .legal-card ol {
            margin: 0 0 18px 22px;
            color: var(--ink-secondary);
        }
        .legal-card li {
            margin-bottom: 8px;
            font-size: 15px;
        }
        .legal-card strong {
            color: var(--ink-primary);
            font-weight: 700;
        }
        .legal-card a {
            color: var(--purple);
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .legal-card a:hover {
            color: var(--dark-purple);
        }

        /* Callout / Notice Boxes */
        .callout-box {
            background: #FBF7FF;
            border-left: 4px solid var(--purple);
            border-radius: 0 10px 10px 0;
            padding: 16px 20px;
            margin: 20px 0 24px;
            font-size: 14.5px;
            color: #372A45;
        }
        .callout-box.info {
            background: #EFF6FF;
            border-left-color: var(--accent-blue);
            color: #1E3A8A;
        }
        .callout-box.warning {
            background: #FFFBEB;
            border-left-color: #F59E0B;
            color: #92400E;
        }
        .callout-box strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14.5px;
        }

        /* Tables for Subprocessors & Permissions */
        .table-wrapper {
            overflow-x: auto;
            margin: 20px 0 26px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
            background: #ffffff;
        }
        th {
            background: #F3EEF9;
            color: var(--dark-purple);
            font-weight: 700;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13.5px;
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            color: var(--ink-secondary);
            vertical-align: top;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) td {
            background: #FAFAFD;
        }

        /* Footer */
        .site-footer {
            background: #1A1323;
            color: rgba(255, 255, 255, 0.7);
            padding: 40px 24px 34px;
            font-size: 13.5px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-inner {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
        }
        .footer-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .footer-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        .footer-contact {
            color: rgba(255, 255, 255, 0.85);
        }
        .footer-contact a {
            color: #CE93D8;
        }
        .back-to-top {
            display: inline-block;
            margin-top: 8px;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.55);
            text-decoration: none;
        }
        .back-to-top:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <nav class="top-nav">
                <a href="https://quizs.in" class="brand-link">
                    Quizs
                    <span class="brand-badge">Legal</span>
                </a>
                <div class="nav-links">
                    <a href="https://quizs.in/privacy" class="{{ request()->is('privacy*') ? 'active' : '' }}">Privacy Policy</a>
                    <a href="https://quizs.in/terms" class="{{ request()->is('terms*') ? 'active' : '' }}">Terms & Conditions</a>
                </div>
            </nav>
            <div class="header-title-block">
                <h1>@yield('title')</h1>
                <div class="header-meta">
                    <span class="meta-pill">📅 Effective: September 19, 2026</span>
                    <span class="meta-pill">📱 App: Quizs (Android &amp; iOS)</span>
                    <span class="meta-pill">🏢 Publisher: Arknox Development Team</span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-title">Table of Contents</div>
            <ul class="sidebar-menu">
                @yield('toc')
            </ul>
        </aside>

        <main class="legal-card">
            @yield('content')
        </main>
    </div>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-links">
                <a href="https://quizs.in/privacy">Privacy Policy</a>
                <a href="https://quizs.in/terms">Terms of Service</a>
                <a href="https://quizs.in">Official Website</a>
            </div>
            <p class="footer-contact">
                Questions or data protection inquiries? Email our Data Protection Officer at
                <a href="mailto:quizsappliaction@gmail.com">quizsappliaction@gmail.com</a>
            </p>
            <p>&copy; {{ date('Y') }} Quizs. All rights reserved. Designed for educational entertainment.</p>
            <a href="#" class="back-to-top">↑ Back to Top</a>
        </div>
    </footer>
</body>
</html>
