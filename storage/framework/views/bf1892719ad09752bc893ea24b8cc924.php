<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Monitoring Dokumen'); ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('images/telkom-property-logo.png.png')); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('images/telkom-property-logo.png.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/telkom-property-logo.png.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('site.webmanifest')); ?>">
    <?php if(($showSidebar ?? true) === false): ?>
        <link rel="preload" as="image" href="<?php echo e(asset('images/bg login regis.png')); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <?php if(($showSidebar ?? true) === false): ?>
        <link rel="stylesheet" href="<?php echo e(asset('css/auth-transitions.css')); ?>">
        <link rel="expect" href="#auth-card" blocking="render">
    <?php endif; ?>
    <style>
        :root {
            color-scheme: light;
            --brand-red: #E11D2A;
            --brand-yellow: #F8D000;
            --brand-gray: #8E8E93;
            --brand-black: #000000;
            --brand-white: #FFFFFF;
            --bg: #F6F7F9;
            --surface: #FFFFFF;
            --surface-soft: #fffdf0;
            --text: #111827;
            --muted: #6B7280;
            --line: #E5E7EB;
            --primary: #DC1F2B;
            --primary-strong: #B0141F;
            --accent: #DC1F2B;
            --primary-soft: #FDECEE;
            --success: #F8D000;
            --warning: #F8D000;
            --danger: #E11D2A;
            --shadow: 0 10px 28px rgba(17, 24, 39, 0.07);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.55;
            letter-spacing: -0.01em;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { color: inherit; }
        h1, h2, h3, th, .label, .value, .nav-title, .page-badge, .button {
            font-family: 'Plus Jakarta Sans', 'Inter', 'Segoe UI', Arial, Helvetica, sans-serif;
            letter-spacing: -0.02em;
        }
        .notification-bell {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid var(--line);
            color: var(--muted);
            text-decoration: none;
            transition: all .2s ease;
            box-shadow: 0 2px 8px rgba(10,40,74,.06);
        }
        .notification-bell:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0,75,141,.12);
        }
        .notification-bell svg {
            width: 20px;
            height: 20px;
        }
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            box-shadow: 0 2px 6px rgba(165,48,48,.3);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .user-menu-trigger:hover,
        .user-menu-trigger:focus-visible {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
        }
        .user-menu-dropdown.is-open { opacity: 1; visibility: visible; transform: translateY(0); }
        .shell {
            display: grid;
            grid-template-columns: 248px minmax(0, 1fr);
            height: 100vh;
            min-height: 0;
            min-width: 0;
            overflow-x: clip;
            transition: grid-template-columns .32s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .shell--no-sidebar {
            grid-template-columns: 1fr;
            height: auto;
            min-height: 100vh;
        }
        .sidebar {
            position: sticky;
            top: 0;
            z-index: 50;
            height: 100vh;
            overflow: visible;
            padding: 24px 16px;
            border-right: 1px solid #E0E3E8;
            background: #ECEEF1;
            color: #374151;
        }
        .brand {
            display: grid;
            gap: 0;
            padding: 14px;
            border: 0;
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(17,24,39,.06);
        }
        .brand-logo {
            width: 100%;
            max-width: 140px;
            margin: 0 auto;
            min-height: 64px;
            border-radius: 16px;
            background: #FFFFFF;
            border: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            box-shadow: none;
            overflow: hidden;
        }
        .brand-logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
            background: transparent;
        }
        .sidebar h1 { margin: 0 0 8px; font-size: 28px; }
        .sidebar p { margin: 0; color: #6B7280; line-height: 1.6; }
        .sidebar-panel {
            margin-top: 24px;
            padding: 18px;
            border-radius: 20px;
            background: #FFFFFF;
            border: 1px solid #E0E3E8;
        }
        .sidebar-panel strong {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .sidebar-panel p {
            font-size: 13px;
        }
        .nav {
            display: grid;
            gap: 6px;
            margin-top: 24px;
        }
        .nav-header {
            padding: 0 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #8A919C;
            margin-bottom: 2px;
        }
        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 14px;
            background: transparent;
            border: 1px solid transparent;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            position: relative;
            transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .nav a:hover { background: #E3E6EA; }
        .nav a:focus-visible { outline: 2px solid #DC1F2B; outline-offset: 2px; }
        .nav a.active { background: #DC1F2B; color: #FFFFFF; font-weight: 700; box-shadow: 0 4px 12px rgba(220,31,43,.25); }
        .nav-title {
            display: block;
            font-weight: 600;
        }
        .nav-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            color: currentColor;
            opacity: 1;
        }
        .content {
            padding: 32px 32px 36px;
            min-width: 0;
            min-height: 0;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .content::-webkit-scrollbar { display: none; }
        .logout-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: min(500px, calc(100% - 32px));
            padding: 14px 18px;
            border: 1px solid #f1df80;
            border-left: 4px solid var(--brand-yellow);
            border-radius: 12px;
            background: #fff9d9;
            color: var(--brand-black);
            box-shadow: 0 10px 28px rgba(10, 40, 74, 0.14);
            transition: opacity .25s ease, transform .25s ease;
        }
        .logout-toast.is-hidden {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }
        .login-error-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            z-index: 1000;
            width: min(500px, calc(100% - 32px));
            padding: 14px 18px;
            border: 1px solid #f3c1c5;
            border-left: 4px solid var(--brand-red);
            border-radius: 12px;
            background: rgba(255, 240, 241, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: var(--brand-black);
            box-shadow: 0 10px 28px rgba(165, 48, 48, 0.14);
            transform: translateX(-50%);
            transition: opacity .25s ease, transform .25s ease;
        }
        .login-error-toast.is-hidden {
            opacity: 0;
            transform: translate(-50%, -10px);
            pointer-events: none;
        }
        .page-head .user-menu { order: 2; }
        .page-head .notification-bell { order: 1; }
        .auth-page {
            overflow: hidden;
            background: #99111b url('<?php echo e(asset('images/bg login regis.png')); ?>') center / cover no-repeat fixed;
        }
        .auth-page .content { min-height: 100vh; padding: 0 22px; overflow: visible; }
        .auth-page .container { min-height: 100%; }
        .page-head h2 { color: var(--brand-black); }
        .container {
            width: min(1500px, 100%);
            margin: 0 auto;
        }
        .page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
            padding: 2px 2px 10px;
        }
        .page-title-group, .page-actions { display: flex; align-items: center; gap: 12px; }
        .mobile-sidebar-toggle { display: none; align-items: center; justify-content: center; width: 40px; height: 40px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); color: var(--text); cursor: pointer; }
        .mobile-sidebar-toggle svg { width: 20px; height: 20px; }
        .user-menu { position: relative; }
        .user-menu-trigger { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 12px; background: var(--surface); border: 1px solid var(--line); color: var(--text); cursor: pointer; font: inherit; font-size: 13px; font-weight: 600; }
        .user-menu-trigger svg:last-child { width: 16px; height: 16px; }
        .user-menu-dropdown { position: absolute; top: calc(100% + 8px); right: 0; min-width: 150px; padding: 4px; background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 10px 30px rgba(17,24,39,.12); opacity: 0; visibility: hidden; transform: translateY(-8px); transition: opacity .2s ease, visibility .2s ease, transform .2s ease; z-index: 100; }
        .user-menu-dropdown form { margin: 0; }
        .logout-menu-item { width: 100%; display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 0; border-radius: 9px; background: transparent; color: #9B1C1C; font: inherit; font-size: 13px; font-weight: 700; text-align: left; cursor: pointer; }
        .logout-menu-item:hover, .logout-menu-item:focus-visible { background: #FDECEC; }
        body:not(.auth-page) { font-family: 'Plus Jakarta Sans', 'Inter', 'Segoe UI', Arial, Helvetica, sans-serif; }
        .page-head h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
        }
        .page-badge {
            display: inline-flex;
            align-items: center;
            padding: 7px 10px;
            border-radius: 999px;
            background: #fff;
            color: var(--primary);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            white-space: nowrap;
            border: 1px solid var(--line);
        }
        .grid {
            display: grid;
            gap: 20px;
        }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .grid-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        .card h2, .card h3 { margin-top: 0; }
        .card h2, .card h3 {
            line-height: 1.25;
        }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); }
        .value { font-size: 30px; font-weight: 800; margin-top: 8px; }
        .muted { color: var(--muted); }
        .compact { font-size: 13px; }
        .stat-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff, #f9fbfd);
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: rgba(0, 75, 141, 0.06);
        }
        .stat-card.warning::after { background: rgba(163, 93, 0, 0.06); }
        .stat-card.info::after { background: rgba(0, 75, 141, 0.06); }
        .stat-card.success::after { background: rgba(22, 124, 89, 0.06); }
        .stat-card.danger::after { background: rgba(165, 48, 48, 0.06); }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        th { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .1em; }
        tbody tr:hover {
            background: #f8fbfe;
        }
        .table-shell {
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #edf2f7;
        }
        .badge.on-process { color: #1D4ED8; background: #E8F0FE; border: 1px solid #BFD3FB; }
        .badge.waiting { color: #92400E; background: #FEF3C7; border: 1px solid #F5DC8A; }
        .badge.return { color: #C2410C; background: #FFF0E5; border: 1px solid #F9CBA6; }
        .badge.approved { color: #15803D; background: #E6F6EC; border: 1px solid #BFE5CC; }
        .badge.rejected { color: #9B1C1C; background: #FDECEC; border: 1px solid #F4C2C2; }
        .badge.success { color: #6D28D9; background: #F0EAFD; border: 1px solid #D8C8F7; }
        .aging.on-process { color: #1D4ED8; background: #E8F0FE; }
        .aging.waiting { color: #92400E; background: #FEF3C7; }
        .aging.return { color: #C2410C; background: #FFF0E5; }
        .aging.approved { color: #15803D; background: #E6F6EC; }
        .aging.rejected { color: #9B1C1C; background: #FDECEC; }
        .aging.success { color: #6D28D9; background: #F0EAFD; }
        .badge.warning { color: #92400E; background: #FEF3C7; border: 1px solid #F5DC8A; }
        .aging { display: inline-block; padding: 3px 7px; border-radius: 8px; border: 1px solid transparent; white-space: nowrap; }
        form { display: grid; gap: 14px; }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px 14px;
            font: inherit;
            font-size: 14px;
            background: #F6F7F9;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: rgba(220, 31, 43, 0.55);
            box-shadow: 0 0 0 4px rgba(220, 31, 43, 0.10);
        }
        textarea { min-height: 120px; resize: vertical; }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            padding: 12px 16px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 0 8px 18px rgba(220, 31, 43, 0.18);
        }
        .button.secondary {
            background: #f0f0f0;
            color: var(--text);
            box-shadow: none;
        }
        .button.success { background: var(--brand-yellow); color: var(--brand-black); }
        .button.danger { background: var(--brand-red); color: var(--brand-white); }
        .button.warning { background: var(--brand-yellow); color: var(--brand-black); }
        .button.small {
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 10px;
        }
        .table-actions {
            display: inline-flex;
            gap: 6px;
            flex-wrap: nowrap;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }
        .section-head h2,
        .section-head h3 {
            margin: 0;
        }
        .notice {
            position: relative;
            margin-bottom: 18px;
             background: #fff9d9;
             color: var(--brand-black);
             border: 1px solid #f1df80;
             border-left: 4px solid var(--brand-yellow);
            padding: 13px 16px;
            border-radius: 12px;
             box-shadow: 0 6px 16px rgba(248,208,0,.10);
            font-size: 13px;
            font-weight: 600;
        }
        .errors {
            position: relative;
            margin-bottom: 18px;
            background: #fff0f1;
            color: var(--brand-black);
            border: 1px solid #f3c1c5;
            border-left: 4px solid var(--brand-red);
            padding: 13px 16px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(225,29,42,.08);
            font-size: 13px;
        }
        .errors strong {
            display: block;
            margin-bottom: 4px;
            font-weight: 700;
        }
        .errors ul {
            margin: 4px 0 0 18px;
            padding: 0;
        }
        .timeline {
            display: grid;
            gap: 14px;
        }
        .timeline-item {
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 16px;
            background: var(--surface-soft);
        }
        .timeline-item header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .kv {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 10px;
            font-size: 14px;
        }
        .kv div:nth-child(odd) { color: var(--muted); }
        body:not(.auth-page) {
            height: 100vh;
            overflow: hidden;
            scrollbar-width: none;
        }
        body:not(.auth-page) * { scrollbar-width: none; }
        body:not(.auth-page) *::-webkit-scrollbar { display: none; }
        .sidebar-toggle {
            position: absolute;
            top: 50%;
            right: -16px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: 1px solid #E5E7EB;
            border-radius: 50%;
            background: #FFFFFF;
            color: #475569;
            box-shadow: 0 2px 6px rgba(17,24,39,.08);
            cursor: pointer;
            transform: translateY(-50%);
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .sidebar-toggle:hover { background: #F8FAFC; color: #475569; }
        .sidebar-toggle svg { width: 18px; height: 18px; transition: transform .25s ease; }
        .sidebar-collapsed { grid-template-columns: 80px minmax(0, 1fr); }
        .sidebar-collapsed .sidebar { padding-left: 16px; padding-right: 16px; }
        .sidebar-collapsed .sidebar-toggle { right: -16px; transform: translateY(-50%); }
        .sidebar-collapsed .sidebar-toggle svg { transform: rotate(180deg); }
        .sidebar-collapsed .brand { padding: 8px; }
        .sidebar-collapsed .brand-logo { max-width: 48px; min-height: 48px; }
        .nav-title {
            overflow: hidden;
            width: auto;
            white-space: nowrap;
            transition: opacity .2s ease, width .32s cubic-bezier(0.22, 1, 0.36, 1), visibility .2s ease;
        }
        .sidebar-collapsed .nav-header,
        .sidebar-collapsed .sidebar-panel { display: none; }
        .sidebar-collapsed .nav-title { width: 0; opacity: 0; visibility: hidden; }
        .sidebar-collapsed .nav a { justify-content: center; padding-left: 15px; padding-right: 15px; }
        .sidebar-collapsed .nav-icon { flex-basis: 18px; width: 18px; height: 18px; }
        .sidebar-backdrop { display: none; }
        @media (max-width: 1023px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 50;
                width: min(248px, 86vw);
                transform: translateX(-100%);
                transition: transform .25s ease;
                padding: 24px 16px;
            }
            .shell.sidebar-open .sidebar { transform: translateX(0); }
            .sidebar-backdrop { position: fixed; inset: 0; z-index: 40; display: block; border: 0; background: rgba(17,24,39,.45); opacity: 0; visibility: hidden; pointer-events: none; transition: opacity .25s ease, visibility .25s ease; }
            .shell.sidebar-open .sidebar-backdrop { opacity: 1; visibility: visible; pointer-events: auto; }
            .mobile-sidebar-toggle { display: inline-flex; }
            .sidebar-toggle { display: none; }
            .sidebar-collapsed { grid-template-columns: 1fr; }
            .sidebar-collapsed .brand { padding: 14px; }
            .sidebar-collapsed .sidebar { padding: 24px 16px; }
            .sidebar-collapsed .brand-logo { max-width: 140px; min-height: 64px; }
            .sidebar-collapsed .nav-header,
            .sidebar-collapsed .nav-title,
            .sidebar-collapsed .sidebar-panel { display: block; }
            .sidebar-collapsed .nav-title { width: auto; opacity: 1; visibility: visible; }
            .sidebar-collapsed .nav a { justify-content: flex-start; padding-left: 12px; padding-right: 12px; }
            .sidebar-collapsed .nav-icon { flex-basis: 18px; width: 18px; height: 18px; }
            .content { padding: 24px; }
            .page-head {
                padding: 18px;
            }
            .grid-2, .grid-3, .grid-4, .grid-5, .grid-6 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .kv { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .page-head {
                flex-direction: column;
                align-items: flex-start;
            }
            .auth-page .content { padding: 0 12px; }
            .auth-page .container { width: 100%; }
        }
    </style>
</head>
<body class="<?php echo e(($showSidebar ?? true) === false ? 'auth-page' : ''); ?>">
    <div class="shell <?php echo e(($showSidebar ?? true) === false ? 'shell--no-sidebar' : ''); ?>">
        <?php if(($showSidebar ?? true) === false): ?>
        <?php else: ?>
        <aside class="sidebar">
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Minimalkan sidebar" aria-expanded="true" title="Minimalkan sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
            </button>
            <div class="brand">
                <div class="brand-logo">
                    <?php if(file_exists(public_path('images/telkom-property-logo.png'))): ?>
                        <img src="<?php echo e(asset('images/telkom-property-logo.png')); ?>" alt="Logo Telkom Property">
                    <?php elseif(file_exists(public_path('images/telkom-property-logo.png.png'))): ?>
                        <img src="<?php echo e(asset('images/telkom-property-logo.png.png')); ?>" alt="Logo Telkom Property">
                    <?php elseif(file_exists(public_path('images/telkom-property-logo.svg'))): ?>
                        <img src="<?php echo e(asset('images/telkom-property-logo.svg')); ?>" alt="Logo Telkom Property">
                    <?php else: ?>
                        TLP
        <?php endif; ?>
    </div>
            </div>
            <div class="nav">
                <div class="nav-header">Navigasi</div>
                <a href="<?php echo e(route('documents.index')); ?>" class="<?php echo e(request()->routeIs('documents.index', 'documents.show', 'documents.edit') ? 'active' : ''); ?>" aria-label="Monitoring" title="Monitoring">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12.5 12 4l9 8.5"></path>
                        <path d="M5 10.5V20h14v-9.5"></path>
                    </svg>
                    <span class="nav-title">Monitoring</span>
                </a>
                <a href="<?php echo e(route('documents.create')); ?>" class="<?php echo e(request()->routeIs('documents.create') ? 'active' : ''); ?>" aria-label="Input Dokumen" title="Input Dokumen">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14"></path>
                        <path d="M5 12h14"></path>
                        <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                    </svg>
                    <span class="nav-title">Input Dokumen</span>
                </a>
            </div>

            <?php if(! file_exists(public_path('images/telkom-property-logo.png')) && ! file_exists(public_path('images/telkom-property-logo.png.png')) && ! file_exists(public_path('images/telkom-property-logo.svg'))): ?>
                <div class="sidebar-panel">
                    <strong>Identitas Brand</strong>
                    <p>Logo Telkom Property belum ditemukan di project. Begitu file logo ditaruh di folder yang saya sarankan, tampilan ini akan otomatis memakainya.</p>
                </div>
            <?php endif; ?>
        </aside>
        <button type="button" class="sidebar-backdrop" id="sidebarBackdrop" aria-label="Tutup navigasi"></button>
        <?php endif; ?>

        <main class="content">
            <div class="container">
                <?php if(($showSidebar ?? true) !== false): ?>
                <section class="page-head">
                    <div class="page-title-group">
                        <button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-label="Buka navigasi" aria-expanded="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h2><?php echo e($title ?? 'Monitoring Dokumen'); ?></h2>
                    </div>
                    <?php if(auth()->check()): ?>
                        <div class="page-actions">
                            <a href="<?php echo e(route('notifications.index')); ?>" class="notification-bell" aria-label="Notifikasi" title="Notifikasi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <?php $unread = auth()->user()?->unreadNotifications()->count() ?? 0; ?>
                                <?php if($unread > 0): ?><span class="notification-badge"><?php echo e($unread > 99 ? '99+' : $unread); ?></span><?php endif; ?>
                            </a>
                            <div class="user-menu">
                                <button type="button" class="user-menu-trigger" aria-expanded="false" aria-haspopup="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span><?php echo e(auth()->user()->name); ?></span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 9l6 6 6-6"></path>
                                    </svg>
                                </button>
                                <div class="user-menu-dropdown" role="menu">
                                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" role="menuitem" class="logout-menu-item">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                <polyline points="16 17 21 12 16 7"></polyline>
                                                <line x1="21" y1="12" x2="9" y2="12"></line>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php if(session('status')): ?>
                    <div class="notice"><?php echo e(session('status')); ?></div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <?php if(request()->routeIs('login')): ?>
                        <div class="login-error-toast" id="loginErrorToast" role="alert">Login Gagal. Silahkan Cek Kembali Email atau Password Anda.</div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>
    <?php if(session('logout_status')): ?>
        <div class="logout-toast" id="logoutToast" role="status"><?php echo e(session('logout_status')); ?></div>
    <?php endif; ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php if(($showSidebar ?? true) === false): ?>
    <script>
        (() => {
            const skipSameDocumentTransition = (event) => {
                const transition = event.viewTransition;
                const fromUrl = event.activation?.from?.url;
                const toUrl = event.activation?.entry?.url;

                if (transition && fromUrl && toUrl && fromUrl === toUrl) {
                    transition.skipTransition();
                }
            };

            if ('ViewTransition' in window) {
                window.addEventListener('pageswap', skipSameDocumentTransition);
                window.addEventListener('pagereveal', skipSameDocumentTransition);
            }
        })();
    </script>
    <?php endif; ?>
    <script>
         const sidebar = document.querySelector('.sidebar');
         const sidebarToggle = document.getElementById('sidebarToggle');
         const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
         const sidebarBackdrop = document.getElementById('sidebarBackdrop');
         const appShell = document.querySelector('.shell:not(.shell--no-sidebar)');

        if (sidebar && sidebarToggle && appShell) {
            const setSidebarCollapsed = (collapsed) => {
                appShell.classList.toggle('sidebar-collapsed', collapsed);
                sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
                sidebarToggle.setAttribute('aria-label', collapsed ? 'Perluas sidebar' : 'Minimalkan sidebar');
                sidebarToggle.title = collapsed ? 'Perluas sidebar' : 'Minimalkan sidebar';
            };

            const storedSidebarState = window.localStorage.getItem('sidebar-collapsed');
            setSidebarCollapsed(storedSidebarState === 'true');

            sidebarToggle.addEventListener('click', function() {
                const collapsed = !appShell.classList.contains('sidebar-collapsed');
                setSidebarCollapsed(collapsed);
                window.localStorage.setItem('sidebar-collapsed', String(collapsed));
             });
         }

          if (mobileSidebarToggle && appShell) {
              const closeMobileSidebar = function() {
                  appShell.classList.remove('sidebar-open');
                  mobileSidebarToggle.setAttribute('aria-expanded', 'false');
              };
              mobileSidebarToggle.addEventListener('click', function() {
                  const isOpen = appShell.classList.toggle('sidebar-open');
                  mobileSidebarToggle.setAttribute('aria-expanded', String(isOpen));
              });
              if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);
          }

        const logoutToast = document.getElementById('logoutToast');
        if (logoutToast) {
            window.setTimeout(function() {
                logoutToast.classList.add('is-hidden');
                window.setTimeout(function() { logoutToast.remove(); }, 300);
            }, 5000);
        }

        const loginErrorToast = document.getElementById('loginErrorToast');
        if (loginErrorToast) {
            const hideLoginError = function() {
                loginErrorToast.classList.add('is-hidden');
                window.setTimeout(function() { loginErrorToast.remove(); }, 300);
            };
            window.setTimeout(function() {
                hideLoginError();
            }, 5000);
        }

        // User menu dropdown - click/tap support
        document.addEventListener('DOMContentLoaded', function() {
            const userMenu = document.querySelector('.user-menu');
            const trigger = document.querySelector('.user-menu-trigger');
            const dropdown = document.querySelector('.user-menu-dropdown');

            if (userMenu && trigger && dropdown) {
                // Toggle on click
                 trigger.addEventListener('click', function(e) {
                     e.stopPropagation();
                     const isOpen = dropdown.classList.contains('is-open');
                    if (isOpen) {
                        closeDropdown();
                    } else {
                        openDropdown();
                    }
                });

                // Close on click outside
                document.addEventListener('click', function(e) {
                    if (!userMenu.contains(e.target)) {
                        closeDropdown();
                    }
                });

                // Close on Escape
                 document.addEventListener('keydown', function(e) {
                     if (e.key === 'Escape') {
                         closeDropdown();
                         trigger.focus();
                     }
                 });

                 function openDropdown() {
                     dropdown.classList.add('is-open');
                     trigger.setAttribute('aria-expanded', 'true');
                     dropdown.querySelector('[role="menuitem"]')?.focus();
                 }

                 function closeDropdown() {
                     dropdown.classList.remove('is-open');
                     trigger.setAttribute('aria-expanded', 'false');
                 }
            }
        });
    </script>
</body>
</html>
<?php /**PATH C:\Projects\monitoring-dokumen\resources\views\layouts\app.blade.php ENDPATH**/ ?>