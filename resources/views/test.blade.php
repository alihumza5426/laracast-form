<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Fee Manager &bull; Multi-Entity Explorer</title>
    <style>
        :root {
            --bg-main: #0b0f19;
            --bg-card: #111827;
            --bg-card-header: #1f2937;
            --bg-input: #0d1322;
            --border-color: #374151;
            --border-focus: #6366f1;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --accent-primary: #4f46e5;
            --accent-hover: #4338ca;
            --danger-bg: rgba(239, 68, 68, 0.15);
            --danger-text: #f87171;
            --danger-border: #dc2626;
            --danger-hover: #b91c1c;
            --success-bg: rgba(16, 185, 129, 0.15);
            --success-text: #34d399;
            --success-border: #059669;
            --warning-bg: rgba(245, 158, 11, 0.15);
            --warning-text: #fbbf24;
            --warning-border: #d97706;
            --font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* Full Screen Auth Lock Overlay */
        #authLockOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, #1e1b4b 0%, #0b0f19 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(12px);
            padding: 1.5rem;
        }

        .auth-card {
            background-color: var(--bg-card);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            padding: 2.25rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 40px rgba(99, 102, 241, 0.15);
            text-align: center;
            animation: modalPop 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .auth-icon-wrap {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.3) 0%, rgba(168, 85, 247, 0.3) 100%);
            border: 1px solid rgba(99, 102, 241, 0.4);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: #818cf8;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .auth-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.4rem;
        }

        .auth-desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .password-field-wrap {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .password-field-wrap input {
            padding-right: 2.75rem;
        }

        .toggle-password-btn {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s;
        }

        .toggle-password-btn:hover {
            color: #ffffff;
        }

        .auth-error-msg {
            background-color: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            font-size: 0.82rem;
            padding: 0.6rem 0.85rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
            align-items: center;
            gap: 0.5rem;
            text-align: left;
        }

        .shake {
            animation: shakeAnim 0.4s ease-in-out;
        }

        @keyframes shakeAnim {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }

        /* Main Application Container */
        #mainAppContainer {
            display: none; /* revealed upon password unlock */
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background: linear-gradient(180deg, #1e1b4b 0%, #0f172a 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.3rem;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .brand-text h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
        }

        .brand-text p {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .badge-dev {
            background-color: rgba(99, 102, 241, 0.2);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.4);
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-weight: 600;
        }

        .btn-lock {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s;
        }

        .btn-lock:hover {
            background-color: #dc2626;
            color: #ffffff;
        }

        .container {
            max-width: 1520px;
            width: 100%;
            margin: 0 auto;
            padding: 1.75rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            flex: 1;
        }

        /* Entity Switcher Tabs */
        .entity-tabs {
            display: flex;
            gap: 0.5rem;
            background-color: #0f172a;
            padding: 0.4rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            overflow-x: auto;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            padding: 0.6rem 1.2rem;
            border-radius: 7px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .tab-btn:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            background-color: var(--bg-card-header);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .card-header h2 {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Filter Controls */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.1rem;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        input[type="date"], input[type="password"], select, input[type="text"], input[type="number"], textarea {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 0.65rem 0.85rem;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
            width: 100%;
            height: 42px;
        }

        textarea {
            height: auto;
            min-height: 80px;
            font-family: inherit;
        }

        input[type="date"]:focus, input[type="password"]:focus, select:focus, input[type="text"]:focus, input[type="number"]:focus, textarea:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .quick-dates {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.35rem;
            align-items: center;
        }

        .btn-quick {
            background: none;
            border: none;
            color: #818cf8;
            font-size: 0.73rem;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            transition: color 0.15s;
        }

        .btn-quick:hover {
            color: #c7d2fe;
        }

        .btn-group-actions {
            display: flex;
            gap: 0.6rem;
            align-items: center;
            margin-top: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.65rem 1.15rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            height: 42px;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background-color: #1f2937;
            color: var(--text-primary);
        }

        .btn-danger {
            background-color: #dc2626;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .action-btns-group {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-action-edit {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 6px;
            padding: 0.3rem 0.55rem;
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s;
        }

        .btn-action-edit:hover {
            background-color: #4f46e5;
            color: #ffffff;
            border-color: #4f46e5;
        }

        .btn-action-delete {
            background: rgba(239, 68, 68, 0.12);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            padding: 0.3rem 0.55rem;
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s;
        }

        .btn-action-delete:hover {
            background-color: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* Bulk Action Bar */
        .bulk-bar {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
            border: 1px solid rgba(99, 102, 241, 0.4);
            border-radius: 8px;
            padding: 0.65rem 1rem;
            display: none;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bulk-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #e0e7ff;
        }

        /* Status & Banner */
        .info-banner {
            background-color: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }

        th {
            background-color: var(--bg-card-header);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 0.85rem 0.95rem;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
            user-select: none;
        }

        td {
            padding: 0.85rem 0.95rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            white-space: nowrap;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.015);
        }

        tr:hover {
            background-color: rgba(99, 102, 241, 0.08);
        }

        tr.selected-row {
            background-color: rgba(99, 102, 241, 0.18) !important;
        }

        .custom-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #6366f1;
        }

        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }

        .status-paid, .status-active, .status-completed, .status-done {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }

        .status-unpaid, .status-inactive, .status-cancelled {
            background-color: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid var(--danger-border);
        }

        .status-pending, .status-in_progress, .status-in-progress, .status-open {
            background-color: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid var(--warning-border);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }

        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* JSON view */
        pre.json-view {
            background-color: #030712;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: #a7f3d0;
            max-height: 400px;
            overflow: auto;
            margin-top: 1rem;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.75);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-box {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            width: 90%;
            max-width: 580px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 1.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #ffffff;
        }

        .modal-desc {
            font-size: 0.88rem;
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            line-height: 1.4;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        /* Toast notification */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast {
            background-color: #1f2937;
            color: #ffffff;
            border: 1px solid var(--border-color);
            border-left: 4px solid #6366f1;
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            font-size: 0.88rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            animation: slideInRight 0.3s ease;
        }

        .toast-success {
            border-left-color: #10b981;
        }

        .toast-danger {
            border-left-color: #ef4444;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        footer {
            text-align: center;
            padding: 1.25rem;
            color: var(--text-muted);
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>

    <!-- 1. Password Access Lock Screen -->
    <div id="authLockOverlay">
        <div class="auth-card" id="authCard">
            <div class="auth-icon-wrap">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="auth-title">Restricted Access</h2>
            <p class="auth-desc">Enter password to access Database Explorer</p>

            <div id="authErrorMsg" class="auth-error-msg">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="authErrorText">Incorrect password. Please try again.</span>
            </div>

            <form id="authLoginForm" onsubmit="event.preventDefault(); submitUnlockPassword();">
                <div class="password-field-wrap">
                    <input type="password" id="authPasswordInput" placeholder="Enter access password..." autocomplete="current-password" autofocus />
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('authPasswordInput', this)" title="Show / Hide Password">
                        <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <button type="submit" id="btnUnlock" class="btn btn-primary" style="width: 100%;">
                    <div id="unlockSpinner" class="spinner"></div>
                    <span id="unlockBtnText">Unlock Dashboard</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 2. Main Authenticated Explorer Page Content -->
    <div id="mainAppContainer">
        <header>
            <div class="brand-container">
                <div class="brand-logo">F</div>
                <div class="brand-text">
                    <h1>Student Fee Manager &bull; Multi-Entity Explorer</h1>
                    <p>Manage Student Fee Manager records, Students, Expenses, and Taskboards with Edit, Delete & Bulk Actions</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="badge-dev">Khan Forms & Table Package</span>
                <button type="button" class="btn-lock" onclick="lockConsole()" title="Lock Dashboard and require password">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Lock
                </button>
            </div>
        </header>

        <main class="container">

            <!-- Entity Switcher Tabs -->
            <div class="entity-tabs">
                <button type="button" class="tab-btn {{ ($entity ?? 'fees') === 'fees' ? 'active' : '' }}" onclick="switchEntity('fees')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Student Fees
                </button>
                <button type="button" class="tab-btn {{ ($entity ?? '') === 'students' ? 'active' : '' }}" onclick="switchEntity('students')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Students
                </button>
                <button type="button" class="tab-btn {{ ($entity ?? '') === 'expenses' ? 'active' : '' }}" onclick="switchEntity('expenses')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Expenses
                </button>
                <button type="button" class="tab-btn {{ ($entity ?? '') === 'taskboards' ? 'active' : '' }}" onclick="switchEntity('taskboards')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Taskboards / Tasks
                </button>
            </div>

            <!-- Advanced Filter & Search Card -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filters & Query Controls (<span id="entityTitleLabel">{{ ucfirst($entity ?? 'fees') }}</span>)
                    </h2>
                    <div class="quick-dates">
                        <button type="button" class="btn-quick" onclick="resetFilters()">Reset to Default (Latest 10)</button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterForm" onsubmit="event.preventDefault(); performSearch();">
                        <input type="hidden" id="currentEntity" name="entity" value="{{ $entity ?? 'fees' }}" />

                        <div class="filter-grid">
                            
                            <!-- 1. Date Picker -->
                            <div class="form-group">
                                <label for="datePicker">
                                    <span>Date</span>
                                    <span style="font-size: 0.72rem; color: var(--text-muted);">(optional)</span>
                                </label>
                                <input type="date" id="datePicker" name="date" value="{{ $selectedDate ?? '' }}" />
                                <div class="quick-dates">
                                    <button type="button" class="btn-quick" onclick="setDate('')">Latest</button>
                                    <span>&bull;</span>
                                    <button type="button" class="btn-quick" onclick="setDate('{{ date('Y-m-d') }}')">Today</button>
                                    <span>&bull;</span>
                                    <button type="button" class="btn-quick" onclick="setDate('{{ date('Y-m-d', strtotime('-1 day')) }}')">Yesterday</button>
                                </div>
                            </div>

                            <!-- 2. Date Column Filter -->
                            <div class="form-group">
                                <label for="dateColumn">Date Column</label>
                                <select id="dateColumn" name="date_column">
                                    <option value="">Auto (Any Date Column)</option>
                                    <option value="created_at" {{ ($dateColumn ?? '') === 'created_at' ? 'selected' : '' }}>Created At</option>
                                    <option value="due_date" {{ ($dateColumn ?? '') === 'due_date' ? 'selected' : '' }}>Due Date</option>
                                    <option value="paid_date" {{ ($dateColumn ?? '') === 'paid_date' ? 'selected' : '' }}>Paid Date</option>
                                    <option value="fee_month" {{ ($dateColumn ?? '') === 'fee_month' ? 'selected' : '' }}>Fee Month</option>
                                    <option value="date" {{ ($dateColumn ?? '') === 'date' ? 'selected' : '' }}>Date</option>
                                </select>
                            </div>

                            <!-- 3. Student Picker / Selector -->
                            <div class="form-group" id="studentPickerGroup">
                                <label for="studentPicker">
                                    <span>Student Filter</span>
                                    <span id="studentCountLabel" style="font-size: 0.72rem; color: #818cf8;">
                                        {{ !empty($students) ? '(' . count($students) . ')' : '' }}
                                    </span>
                                </label>
                                <select id="studentPicker" name="student_id">
                                    <option value="">-- All Students --</option>
                                    @if(!empty($students))
                                        @foreach($students as $st)
                                            @php
                                                $stId = $st['id'] ?? '';
                                                $stName = $st['name'] ?? ($st['student_name'] ?? '');
                                                $stRoll = $st['roll_no'] ?? '';
                                                $label = "Student #{$stId}" . ($stName ? " - {$stName}" : "") . ($stRoll ? " (Roll: {$stRoll})" : "");
                                            @endphp
                                            <option value="{{ $stId }}" {{ ($studentId ?? '') == $stId ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- 4. Type / Sub-type Filter -->
                            <div class="form-group">
                                <label for="typeFilter">Sub-Type</label>
                                <select id="typeFilter" name="type">
                                    <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>All Sub-Types</option>
                                    <option value="challan" {{ ($type ?? '') === 'challan' ? 'selected' : '' }}>Challan</option>
                                    <option value="student" {{ ($type ?? '') === 'student' ? 'selected' : '' }}>Student Specific</option>
                                </select>
                            </div>

                            <!-- 5. Status Filter -->
                            <div class="form-group">
                                <label for="statusFilter">Status</label>
                                <select id="statusFilter" name="status">
                                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Paid / Done</option>
                                    <option value="unpaid" {{ ($status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending / Open</option>
                                    <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>

                            <!-- 6. Keyword Search -->
                            <div class="form-group">
                                <label for="searchInput">Keyword Search</label>
                                <input type="text" id="searchInput" name="search" placeholder="Search by name, title, roll #, ID..." value="{{ $search ?? '' }}" />
                            </div>

                            <!-- 7. Limit -->
                            <div class="form-group" style="max-width: 120px;">
                                <label for="limitInput">Limit</label>
                                <select id="limitInput" name="limit">
                                    <option value="10" {{ ($limit ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ ($limit ?? 10) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($limit ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ ($limit ?? 10) == 100 ? 'selected' : '' }}>100</option>
                                    <option value="500" {{ ($limit ?? 10) == 500 ? 'selected' : '' }}>500 (All)</option>
                                </select>
                            </div>

                            <!-- 8. Search / Apply Actions -->
                            <div class="btn-group-actions">
                                <button type="submit" id="btnSearch" class="btn btn-primary">
                                    <div id="btnSpinner" class="spinner"></div>
                                    <span id="btnSearchText">Apply Filters</span>
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Card with Bulk Delete & Checkmarks -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span id="cardTableTitle">{{ ucfirst($entity ?? 'fees') }} Records</span>
                    </h2>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; height: 34px;" onclick="toggleJsonView()">
                            Toggle Raw JSON
                        </button>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Floating Bulk Action Toolbar -->
                    <div id="bulkBar" class="bulk-bar">
                        <div class="bulk-info">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span id="bulkSelectedText">0 entries selected</span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" class="btn btn-danger" style="height: 34px; font-size: 0.82rem; padding: 0.35rem 0.85rem;" onclick="promptBulkDelete()">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Selected (<span id="bulkCountSpan">0</span>)
                            </button>
                            <button type="button" class="btn btn-secondary" style="height: 34px; font-size: 0.82rem; padding: 0.35rem 0.85rem;" onclick="clearSelection()">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <!-- Info Status Banner -->
                    <div id="infoBanner" class="info-banner">
                        <span id="statusMessage">{{ $message ?? 'Showing latest records.' }}</span>
                        <strong id="recordCountBadge" style="background: rgba(99, 102, 241, 0.2); padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid rgba(99, 102, 241, 0.4);">
                            Count: {{ $count ?? count($records ?? []) }}
                        </strong>
                    </div>

                    <!-- Main Data Table -->
                    <div class="table-responsive">
                        <table id="mainTable">
                            <thead id="tableHead">
                                <!-- Headers dynamically injected or blade rendered -->
                            </thead>
                            <tbody id="tableBody">
                                <!-- Rows dynamically injected or blade rendered -->
                            </tbody>
                        </table>
                    </div>

                    <div id="jsonContainer" style="display: none;">
                        <pre id="jsonBlock" class="json-view">{{ json_encode($records ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            </div>
        </main>

        <!-- Edit Entry Modal -->
        <div id="editModal" class="modal-overlay">
            <div class="modal-box">
                <h3 class="modal-title" id="editModalTitle">
                    <svg width="22" height="22" fill="none" stroke="#818cf8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Record
                </h3>
                <p class="modal-desc" id="editModalDesc">Modify fields and click Save Changes to update this database entry.</p>
                
                <form id="editForm" onsubmit="event.preventDefault(); submitEditEntry();">
                    <input type="hidden" id="editEntryId" name="id" />
                    <div id="editFormFields" style="display: flex; flex-direction: column; gap: 0.9rem;">
                        <!-- Dynamically populated form fields -->
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" id="btnSaveEdit" class="btn btn-primary">
                            <div id="editSpinner" class="spinner"></div>
                            <span id="editBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Delete Modal -->
        <div id="confirmModal" class="modal-overlay">
            <div class="modal-box">
                <h3 class="modal-title" id="modalTitle">
                    <svg width="22" height="22" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Confirm Delete
                </h3>
                <p class="modal-desc" id="modalDescription">Are you sure you want to delete this record? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                    <button type="button" id="btnConfirmDeleteAction" class="btn btn-danger" onclick="executePendingDelete()">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notifications Container -->
        <div id="toastContainer" class="toast-container"></div>

        <footer>
            <p>Khan Forms & Table Package &bull; Multi-Entity Explorer &bull; Students, Fees, Expenses, Taskboards</p>
        </footer>
    </div>

    <script>
        // ============================================
        // PASSWORD AUTHENTICATION (DECODED RUNTIME)
        // ============================================
        // Stored in encoded Base64 form: "c2xzMTIzNDU2" -> decodes to "sls123456"
        const ENCODED_PASS = 'c2xzMTIzNDU2';
        const AUTH_STORAGE_KEY = 'forms_explorer_auth_v1';

        function checkAuthStatus() {
            const isAuth = sessionStorage.getItem(AUTH_STORAGE_KEY) === 'granted';
            const overlay = document.getElementById('authLockOverlay');
            const mainApp = document.getElementById('mainAppContainer');

            if (isAuth) {
                overlay.style.display = 'none';
                mainApp.style.display = 'flex';
            } else {
                overlay.style.display = 'flex';
                mainApp.style.display = 'none';
                setTimeout(() => {
                    const passInput = document.getElementById('authPasswordInput');
                    if (passInput) passInput.focus();
                }, 100);
            }
        }

        function submitUnlockPassword() {
            const inputEl = document.getElementById('authPasswordInput');
            const entered = inputEl.value;
            const errorMsg = document.getElementById('authErrorMsg');
            const authCard = document.getElementById('authCard');
            const btnSpinner = document.getElementById('unlockSpinner');
            const btnText = document.getElementById('unlockBtnText');

            btnSpinner.style.display = 'inline-block';
            btnText.textContent = 'Verifying...';

            setTimeout(() => {
                const correctPass = atob(ENCODED_PASS); // Decodes to "sls123456"

                if (entered === correctPass) {
                    sessionStorage.setItem(AUTH_STORAGE_KEY, 'granted');
                    errorMsg.style.display = 'none';
                    
                    // Smooth transition
                    const overlay = document.getElementById('authLockOverlay');
                    const mainApp = document.getElementById('mainAppContainer');
                    overlay.style.transition = 'opacity 0.3s';
                    overlay.style.opacity = '0';

                    setTimeout(() => {
                        overlay.style.display = 'none';
                        mainApp.style.display = 'flex';
                        renderHeadersAndRows(currentEntity, currentRecords);
                    }, 300);
                } else {
                    errorMsg.style.display = 'flex';
                    authCard.classList.remove('shake');
                    void authCard.offsetWidth; // trigger reflow
                    authCard.classList.add('shake');
                    inputEl.value = '';
                    inputEl.focus();
                }

                btnSpinner.style.display = 'none';
                btnText.textContent = 'Unlock Dashboard';
            }, 250);
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            btn.innerHTML = isPassword 
                ? '<svg width="18" height="18" fill="none" stroke="#818cf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>'
                : '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        }

        function lockConsole() {
            sessionStorage.removeItem(AUTH_STORAGE_KEY);
            const overlay = document.getElementById('authLockOverlay');
            const mainApp = document.getElementById('mainAppContainer');
            const passInput = document.getElementById('authPasswordInput');
            const errorMsg = document.getElementById('authErrorMsg');

            if (passInput) passInput.value = '';
            if (errorMsg) errorMsg.style.display = 'none';

            overlay.style.opacity = '1';
            overlay.style.display = 'flex';
            mainApp.style.display = 'none';

            if (passInput) passInput.focus();
        }

        // ============================================
        // APPLICATION STATE & DATA EXPLORER
        // ============================================
        let currentEntity = @json($entity ?? 'fees');
        let currentRecords = @json($records ?? []);
        let selectedIds = new Set();
        let pendingDeleteAction = null; // { type: 'single'|'bulk', id?: string, ids?: string[] }

        // Initialize table and check auth on load
        document.addEventListener('DOMContentLoaded', () => {
            checkAuthStatus();
            renderHeadersAndRows(currentEntity, currentRecords);
        });

        function switchEntity(entityName) {
            currentEntity = entityName;
            document.getElementById('currentEntity').value = entityName;

            // Update Tab UI
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            // Update Labels
            const titles = {
                fees: 'Student Fees',
                students: 'Students',
                expenses: 'Expenses',
                taskboards: 'Taskboards / Tasks'
            };
            const label = titles[entityName] || entityName;
            document.getElementById('entityTitleLabel').textContent = label;
            document.getElementById('cardTableTitle').textContent = label + ' Records';

            // Show / Hide student picker based on entity
            const studentGroup = document.getElementById('studentPickerGroup');
            if (studentGroup) {
                studentGroup.style.display = (entityName === 'fees' || entityName === 'students') ? 'flex' : 'none';
            }

            clearSelection();
            performSearch();
        }

        function setDate(dateStr) {
            document.getElementById('datePicker').value = dateStr;
            performSearch();
        }

        function resetFilters() {
            document.getElementById('filterForm').reset();
            document.getElementById('datePicker').value = '';
            document.getElementById('typeFilter').value = 'all';
            document.getElementById('studentPicker').value = '';
            document.getElementById('dateColumn').value = '';
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('searchInput').value = '';
            document.getElementById('limitInput').value = '10';
            clearSelection();
            performSearch();
        }

        function toggleJsonView() {
            const el = document.getElementById('jsonContainer');
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }

        // ============================
        // SEARCH & FILTER FUNCTIONALITY
        // ============================
        async function performSearch() {
            const entity = currentEntity;
            const dateVal = document.getElementById('datePicker').value;
            const columnVal = document.getElementById('dateColumn').value;
            const typeVal = document.getElementById('typeFilter').value;
            const studentIdVal = document.getElementById('studentPicker').value;
            const statusVal = document.getElementById('statusFilter').value;
            const searchVal = document.getElementById('searchInput').value;
            const limitVal = document.getElementById('limitInput').value || 10;

            const btnSpinner = document.getElementById('btnSpinner');
            const btnText = document.getElementById('btnSearchText');
            const statusMessage = document.getElementById('statusMessage');
            const recordCountBadge = document.getElementById('recordCountBadge');
            const jsonBlock = document.getElementById('jsonBlock');

            btnSpinner.style.display = 'inline-block';
            btnText.textContent = 'Filtering...';

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch("{{ route('forms.test.search') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        entity: entity,
                        date: dateVal || null,
                        date_column: columnVal || null,
                        type: typeVal || 'all',
                        student_id: studentIdVal || null,
                        status: statusVal || 'all',
                        search: searchVal || null,
                        limit: limitVal
                    })
                });

                const res = await response.json();
                currentRecords = res.data || [];

                statusMessage.textContent = res.message || `Retrieved ${currentRecords.length} records.`;
                recordCountBadge.textContent = 'Count: ' + (res.count ?? currentRecords.length);
                jsonBlock.textContent = JSON.stringify(currentRecords, null, 2);

                clearSelection();
                renderHeadersAndRows(entity, currentRecords);

            } catch (err) {
                statusMessage.textContent = 'Error: ' + err.message;
                showToast('Failed to apply filters: ' + err.message, 'danger');
            } finally {
                btnSpinner.style.display = 'none';
                btnText.textContent = 'Apply Filters';
            }
        }

        // ============================
        // DYNAMIC TABLE RENDERING
        // ============================
        function renderHeadersAndRows(entity, records) {
            const thead = document.getElementById('tableHead');
            const tbody = document.getElementById('tableBody');
            thead.innerHTML = '';
            tbody.innerHTML = '';

            let headers = [];

            if (entity === 'fees') {
                headers = ['# ID', 'Student', 'Title / Challan', 'Total Amount', 'Fee', 'Fine', 'Discount', 'Status', 'Fee Month', 'Due Date', 'Created At'];
            } else if (entity === 'students') {
                headers = ['# ID', 'Name', 'Roll / Reg #', 'Email', 'Phone', 'Created At'];
            } else if (entity === 'expenses') {
                headers = ['# ID', 'Title / Item', 'Amount', 'Category', 'Date', 'Status', 'Description', 'Created At'];
            } else if (entity === 'taskboards') {
                headers = ['# ID', 'Task / Board Title', 'Status', 'Priority', 'Assigned To', 'Due Date', 'Created At'];
            } else {
                if (records && records.length > 0) {
                    headers = Object.keys(records[0]).filter(k => k !== 'id').map(k => k.replace(/_/g, ' ').toUpperCase());
                    headers.unshift('# ID');
                } else {
                    headers = ['# ID', 'Title / Name', 'Status', 'Created At'];
                }
            }

            // Render Header
            let headHtml = `<tr>
                <th style="width: 40px; text-align: center;">
                    <input type="checkbox" id="selectAllCheckbox" class="custom-checkbox" onchange="toggleSelectAll(this)" title="Select / Deselect all" />
                </th>`;
            headers.forEach(h => {
                headHtml += `<th>${escapeHtml(h)}</th>`;
            });
            headHtml += `<th style="text-align: center; width: 140px;">Actions</th></tr>`;
            thead.innerHTML = headHtml;

            // Render Body
            if (!records || records.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${headers.length + 2}" class="empty-state">
                            No records found for [${escapeHtml(entity)}] matching the criteria.
                        </td>
                    </tr>
                `;
                return;
            }

            records.forEach(row => {
                const tr = document.createElement('tr');
                const rowId = row.id || '';
                tr.id = `row-${rowId}`;
                if (selectedIds.has(String(rowId))) {
                    tr.classList.add('selected-row');
                }

                const isChecked = selectedIds.has(String(rowId)) ? 'checked' : '';
                let cellsHtml = `<td style="text-align: center;">
                    <input type="checkbox" class="row-checkbox custom-checkbox" value="${rowId}" ${isChecked} onchange="handleRowCheckboxChange(this, '${rowId}')" />
                </td>`;

                if (entity === 'fees') {
                    const st = (row.status || 'unpaid').toLowerCase();
                    const badgeClass = st === 'paid' ? 'status-paid' : (st === 'unpaid' ? 'status-unpaid' : 'status-pending');
                    cellsHtml += `
                        <td><strong>#${escapeHtml(rowId)}</strong></td>
                        <td>
                            <span style="font-weight: 600; color: #818cf8;">Student #${escapeHtml(row.student_id || '-')}</span>
                            ${row.student_name ? `<div style="font-size: 0.75rem; color: var(--text-secondary);">${escapeHtml(row.student_name)}</div>` : ''}
                        </td>
                        <td>
                            <span>${escapeHtml(row.title || '-')}</span>
                            ${row.challan_no ? `<div style="font-size: 0.75rem; color: #34d399;">Challan: ${escapeHtml(row.challan_no)}</div>` : ''}
                        </td>
                        <td><strong>${formatMoney(row.total_amount)}</strong></td>
                        <td>${formatMoney(row.fee)}</td>
                        <td>${formatMoney(row.fine)}</td>
                        <td>${formatMoney(row.discount)}</td>
                        <td><span class="status-badge ${badgeClass}">${escapeHtml(row.status || 'N/A')}</span></td>
                        <td>${escapeHtml(row.fee_month || '-')}</td>
                        <td>${escapeHtml(row.due_date || '-')}</td>
                        <td>${escapeHtml(row.created_at || '-')}</td>
                    `;
                } else if (entity === 'students') {
                    cellsHtml += `
                        <td><strong>#${escapeHtml(rowId)}</strong></td>
                        <td><strong style="color: #818cf8;">${escapeHtml(row.name || row.student_name || row.full_name || '-')}</strong></td>
                        <td>${escapeHtml(row.roll_no || row.registration_no || '-')}</td>
                        <td>${escapeHtml(row.email || '-')}</td>
                        <td>${escapeHtml(row.phone || row.mobile || '-')}</td>
                        <td>${escapeHtml(row.created_at || '-')}</td>
                    `;
                } else if (entity === 'expenses') {
                    const st = (row.status || 'paid').toLowerCase();
                    const badgeClass = st === 'paid' ? 'status-paid' : (st === 'unpaid' ? 'status-unpaid' : 'status-pending');
                    cellsHtml += `
                        <td><strong>#${escapeHtml(rowId)}</strong></td>
                        <td><strong>${escapeHtml(row.title || row.name || row.item_name || '-')}</strong></td>
                        <td><strong>${formatMoney(row.amount || row.total_amount)}</strong></td>
                        <td><span style="color: #fbbf24;">${escapeHtml(row.category || row.expense_category || '-')}</span></td>
                        <td>${escapeHtml(row.date || row.expense_date || '-')}</td>
                        <td><span class="status-badge ${badgeClass}">${escapeHtml(row.status || 'PAID')}</span></td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(row.description || row.remarks || '-')}</td>
                        <td>${escapeHtml(row.created_at || '-')}</td>
                    `;
                } else if (entity === 'taskboards') {
                    const st = (row.status || 'pending').toLowerCase();
                    const badgeClass = (st === 'completed' || st === 'done') ? 'status-completed' : ((st === 'in_progress' || st === 'in-progress') ? 'status-in_progress' : 'status-pending');
                    cellsHtml += `
                        <td><strong>#${escapeHtml(rowId)}</strong></td>
                        <td><strong>${escapeHtml(row.title || row.task_name || row.name || '-')}</strong></td>
                        <td><span class="status-badge ${badgeClass}">${escapeHtml(row.status || 'Pending')}</span></td>
                        <td>${escapeHtml(row.priority || '-')}</td>
                        <td>${escapeHtml(row.assigned_to || row.user_id || '-')}</td>
                        <td>${escapeHtml(row.due_date || row.deadline || '-')}</td>
                        <td>${escapeHtml(row.created_at || '-')}</td>
                    `;
                } else {
                    cellsHtml += `<td><strong>#${escapeHtml(rowId)}</strong></td>`;
                    headers.slice(1).forEach(h => {
                        const key = h.toLowerCase().replace(/ /g, '_');
                        cellsHtml += `<td>${escapeHtml(row[key] ?? '-')}</td>`;
                    });
                }

                // Action Buttons Column
                cellsHtml += `
                    <td style="text-align: center;">
                        <div class="action-btns-group">
                            <button type="button" class="btn-action-edit" onclick="openEditModal('${rowId}')" title="Edit entry #${rowId}">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <button type="button" class="btn-action-delete" onclick="promptSingleDelete('${rowId}')" title="Delete entry #${rowId}">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </td>
                `;

                tr.innerHTML = cellsHtml;
                tbody.appendChild(tr);
            });

            updateBulkBar();
        }

        // ============================
        // CHECKMARK & SELECTION LOGIC
        // ============================
        function handleRowCheckboxChange(checkbox, rowId) {
            const tr = document.getElementById(`row-${rowId}`);
            if (checkbox.checked) {
                selectedIds.add(String(rowId));
                if (tr) tr.classList.add('selected-row');
            } else {
                selectedIds.delete(String(rowId));
                if (tr) tr.classList.remove('selected-row');
            }
            updateBulkBar();
        }

        function toggleSelectAll(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = masterCheckbox.checked;
                const rowId = cb.value;
                const tr = document.getElementById(`row-${rowId}`);
                if (masterCheckbox.checked) {
                    selectedIds.add(String(rowId));
                    if (tr) tr.classList.add('selected-row');
                } else {
                    selectedIds.delete(String(rowId));
                    if (tr) tr.classList.remove('selected-row');
                }
            });
            updateBulkBar();
        }

        function clearSelection() {
            selectedIds.clear();
            const master = document.getElementById('selectAllCheckbox');
            if (master) master.checked = false;
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('tr.selected-row').forEach(tr => tr.classList.remove('selected-row'));
            updateBulkBar();
        }

        function updateBulkBar() {
            const bulkBar = document.getElementById('bulkBar');
            const bulkSelectedText = document.getElementById('bulkSelectedText');
            const bulkCountSpan = document.getElementById('bulkCountSpan');
            const masterCheckbox = document.getElementById('selectAllCheckbox');

            const count = selectedIds.size;
            if (count > 0) {
                bulkBar.style.display = 'flex';
                bulkSelectedText.textContent = `${count} ${count === 1 ? 'entry' : 'entries'} selected`;
                bulkCountSpan.textContent = count;
            } else {
                bulkBar.style.display = 'none';
            }

            const totalRows = document.querySelectorAll('.row-checkbox').length;
            if (masterCheckbox) {
                masterCheckbox.checked = totalRows > 0 && count === totalRows;
                masterCheckbox.indeterminate = count > 0 && count < totalRows;
            }
        }

        // ============================
        // EDIT RECORD FUNCTIONALITY
        // ============================
        function openEditModal(id) {
            const record = currentRecords.find(r => String(r.id) === String(id));
            if (!record) {
                showToast('Record not found.', 'danger');
                return;
            }

            document.getElementById('editEntryId').value = id;
            document.getElementById('editModalTitle').innerHTML = `
                <svg width="22" height="22" fill="none" stroke="#818cf8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit ${ucfirst(currentEntity)} Entry #${id}
            `;

            const container = document.getElementById('editFormFields');
            container.innerHTML = '';

            const ignoreKeys = ['id', 'created_at', 'updated_at'];
            Object.keys(record).forEach(key => {
                if (ignoreKeys.includes(key)) return;

                const val = record[key] ?? '';
                const formGroup = document.createElement('div');
                formGroup.className = 'form-group';

                const label = document.createElement('label');
                label.textContent = key.replace(/_/g, ' ').toUpperCase();

                let input;
                if (key === 'description' || key === 'remarks') {
                    input = document.createElement('textarea');
                    input.rows = 3;
                    input.value = val;
                } else if (key.includes('date') || key === 'fee_month') {
                    input = document.createElement('input');
                    input.type = (key === 'fee_month' && String(val).length === 7) ? 'month' : 'text';
                    input.value = val;
                } else if (key === 'status') {
                    input = document.createElement('select');
                    const options = ['paid', 'unpaid', 'pending', 'active', 'inactive', 'completed', 'in_progress', 'cancelled'];
                    options.forEach(opt => {
                        const op = document.createElement('option');
                        op.value = opt;
                        op.textContent = opt.toUpperCase();
                        if (String(val).toLowerCase() === opt) op.selected = true;
                        input.appendChild(op);
                    });
                } else if (key.includes('amount') || key === 'fee' || key === 'fine' || key === 'discount') {
                    input = document.createElement('input');
                    input.type = 'number';
                    input.step = '0.01';
                    input.value = val;
                } else {
                    input = document.createElement('input');
                    input.type = 'text';
                    input.value = val;
                }

                input.name = key;
                input.id = `edit_field_${key}`;
                formGroup.appendChild(label);
                formGroup.appendChild(input);
                container.appendChild(formGroup);
            });

            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        async function submitEditEntry() {
            const id = document.getElementById('editEntryId').value;
            const form = document.getElementById('editForm');
            const formData = new FormData(form);
            const payload = {};
            formData.forEach((value, key) => {
                payload[key] = value;
            });
            payload['entity'] = currentEntity;

            const editSpinner = document.getElementById('editSpinner');
            const editBtnText = document.getElementById('editBtnText');
            const btnSave = document.getElementById('btnSaveEdit');

            editSpinner.style.display = 'inline-block';
            editBtnText.textContent = 'Saving...';
            btnSave.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/forms/test/update/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(payload)
                });

                const res = await response.json();
                if (response.ok && res.passed) {
                    showToast(res.message || `Record #${id} updated successfully.`, 'success');
                    
                    if (res.data) {
                        const idx = currentRecords.findIndex(r => String(r.id) === String(id));
                        if (idx !== -1) {
                            currentRecords[idx] = { ...currentRecords[idx], ...res.data };
                        }
                    }
                    renderHeadersAndRows(currentEntity, currentRecords);
                    document.getElementById('jsonBlock').textContent = JSON.stringify(currentRecords, null, 2);
                    closeEditModal();
                } else {
                    showToast(res.message || 'Failed to update record.', 'danger');
                }
            } catch (err) {
                showToast('Error during update: ' + err.message, 'danger');
            } finally {
                editSpinner.style.display = 'none';
                editBtnText.textContent = 'Save Changes';
                btnSave.disabled = false;
            }
        }

        // ============================
        // DELETE ENTRY FUNCTIONALITY
        // ============================
        function promptSingleDelete(id) {
            pendingDeleteAction = { type: 'single', id: id };
            document.getElementById('modalTitle').textContent = `Delete ${ucfirst(currentEntity)} #${id}`;
            document.getElementById('modalDescription').textContent = `Are you sure you want to delete record #${id} from the database? This action cannot be undone.`;
            openConfirmModal();
        }

        function promptBulkDelete() {
            const count = selectedIds.size;
            if (count === 0) return;

            pendingDeleteAction = { type: 'bulk', ids: Array.from(selectedIds) };
            document.getElementById('modalTitle').textContent = `Delete ${count} Selected ${ucfirst(currentEntity)} Entries`;
            document.getElementById('modalDescription').textContent = `Are you sure you want to permanently delete all ${count} selected records from the database?`;
            openConfirmModal();
        }

        function openConfirmModal() {
            document.getElementById('confirmModal').style.display = 'flex';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            pendingDeleteAction = null;
        }

        async function executePendingDelete() {
            if (!pendingDeleteAction) return;

            const btnConfirm = document.getElementById('btnConfirmDeleteAction');
            const origText = btnConfirm.textContent;
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Deleting...';

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                if (pendingDeleteAction.type === 'single') {
                    const id = pendingDeleteAction.id;
                    const response = await fetch(`/forms/test/delete/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'X-HTTP-Method-Override': 'DELETE'
                        },
                        body: JSON.stringify({ entity: currentEntity })
                    });

                    const res = await response.json();
                    if (response.ok && res.passed) {
                        showToast(`Record #${id} deleted successfully.`, 'success');
                        selectedIds.delete(String(id));
                        removeRowFromDom(id);
                    } else {
                        showToast(res.message || 'Failed to delete record.', 'danger');
                    }
                } else if (pendingDeleteAction.type === 'bulk') {
                    const ids = pendingDeleteAction.ids;
                    const response = await fetch("{{ route('forms.test.delete.bulk') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify({ entity: currentEntity, ids: ids })
                    });

                    const res = await response.json();
                    if (response.ok && res.passed) {
                        showToast(res.message || `Deleted ${ids.length} records.`, 'success');
                        ids.forEach(id => {
                            selectedIds.delete(String(id));
                            removeRowFromDom(id);
                        });
                    } else {
                        showToast(res.message || 'Failed to delete selected records.', 'danger');
                    }
                }
            } catch (err) {
                showToast('Error during deletion: ' + err.message, 'danger');
            } finally {
                btnConfirm.disabled = false;
                btnConfirm.textContent = origText;
                closeConfirmModal();
                updateBulkBar();
                updateRecordCountBadge();
            }
        }

        function removeRowFromDom(id) {
            const tr = document.getElementById(`row-${id}`);
            if (tr) {
                tr.style.transition = 'all 0.3s';
                tr.style.opacity = '0';
                tr.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    tr.remove();
                    const remaining = document.querySelectorAll('#tableBody tr');
                    if (remaining.length === 0) {
                        renderHeadersAndRows(currentEntity, []);
                    }
                }, 300);
            }
            currentRecords = currentRecords.filter(r => String(r.id) !== String(id));
            document.getElementById('jsonBlock').textContent = JSON.stringify(currentRecords, null, 2);
        }

        function updateRecordCountBadge() {
            const total = document.querySelectorAll('#tableBody tr:not(:has(.empty-state))').length;
            document.getElementById('recordCountBadge').textContent = 'Count: ' + total;
        }

        // ============================
        // TOAST NOTIFICATIONS & HELPERS
        // ============================
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icon = type === 'success' 
                ? '<svg width="18" height="18" fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : '<svg width="18" height="18" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

            toast.innerHTML = `${icon}<span>${escapeHtml(message)}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'all 0.3s';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function formatMoney(val) {
            if (val === null || val === undefined || val === '') return '-';
            const num = parseFloat(val);
            if (isNaN(num)) return escapeHtml(val);
            return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function ucfirst(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
</body>
</html>
