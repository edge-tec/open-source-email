<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EdgeMail Installer - @yield('title', 'Setup')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #111827;
            --bg-card: #1a1f35;
            --bg-card-hover: #1f2544;
            --border-color: #2a3154;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #6366f1;
            --accent-hover: #818cf8;
            --accent-glow: rgba(99, 102, 241, 0.3);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.1);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.1);
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
        }

        .installer-container {
            width: 100%;
            max-width: 720px;
        }

        /* Logo & Branding */
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 8px 32px var(--accent-glow);
        }
        .brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-hover));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .brand p { color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem; }

        /* Progress Steps */
        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .step-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            border: 2px solid var(--border-color);
            background: var(--bg-secondary);
            transition: all 0.3s;
        }
        .step.active .step-num {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
            box-shadow: 0 0 16px var(--accent-glow);
        }
        .step.completed .step-num {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }
        .step.active .step-label { color: var(--text-primary); }
        .step.completed .step-label { color: var(--success); }
        .step-line {
            width: 24px;
            height: 2px;
            background: var(--border-color);
            margin: 0 0.25rem;
            border-radius: 1px;
        }
        .step.completed + .step-line,
        .step-line.completed { background: var(--success); }

        /* Card */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .card-desc {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* Form elements */
        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .form-input {
            width: 100%;
            padding: 0.7rem 1rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-input::placeholder { color: var(--text-muted); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: white;
            box-shadow: 0 4px 16px var(--accent-glow);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px var(--accent-glow);
        }
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
        }
        .btn-success {
            background: var(--success);
            color: white;
        }
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        .btn-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* Check list */
        .check-list { list-style: none; }
        .check-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 0.875rem;
        }
        .check-item:last-child { border-bottom: none; }
        .check-icon {
            width: 22px; height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            flex-shrink: 0;
        }
        .check-pass { background: var(--success-bg); color: var(--success); }
        .check-fail { background: var(--danger-bg); color: var(--danger); }
        .check-warn { background: var(--warning-bg); color: var(--warning); }
        .check-label { flex: 1; }
        .check-value { color: var(--text-muted); font-size: 0.8rem; font-family: monospace; }

        /* Alert box */
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success { background: var(--success-bg); color: var(--success); border: 1px solid rgba(16,185,129,0.2); }
        .alert-danger { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); }
        .alert-warning { background: var(--warning-bg); color: var(--warning); border: 1px solid rgba(245,158,11,0.2); }
        .alert-info { background: rgba(59,130,246,0.1); color: var(--info); border: 1px solid rgba(59,130,246,0.2); }

        /* Progress bar */
        .progress-bar {
            width: 100%;
            height: 6px;
            background: var(--bg-secondary);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), #8b5cf6);
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        /* Spinner */
        .spinner {
            width: 20px; height: 20px;
            border: 2px solid var(--border-color);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Log output */
        .log-output {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--text-secondary);
            max-height: 300px;
            overflow-y: auto;
            line-height: 1.8;
        }
        .log-output .log-success { color: var(--success); }
        .log-output .log-error { color: var(--danger); }
        .log-output .log-info { color: var(--info); }

        /* Responsive */
        @media (max-width: 640px) {
            .card { padding: 1.5rem; }
            .form-row { grid-template-columns: 1fr; }
            .step-label { display: none; }
            .steps { gap: 0; }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="brand">
            <img src="{{ asset('images/logo.png') }}" alt="EdgeMail" style="height: 60px; margin-bottom: 0.5rem;">
            <p>Open Source Email Server</p>
        </div>

        @yield('steps')

        <div class="card">
            @yield('content')
        </div>
    </div>

    <script>
        // CSRF token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        async function apiPost(url, data) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });
            return response.json();
        }
    </script>
    @yield('scripts')
</body>
</html>
