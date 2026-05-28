<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - EdgeMail Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#f8fafc;--bg2:#ffffff;--bg3:#f1f5f9;--bg4:#e2e8f0;--border:#e2e8f0;--t1:#0f172a;--t2:#475569;--t3:#64748b;--accent:#4f46e5;--accent2:#6366f1;--glow:rgba(79,70,229,.2);--ok:#10b981;--err:#ef4444;--warn:#f59e0b;--info:#3b82f6;--sidebar-w:260px}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--t1);display:flex;min-height:100vh}
        body.sidebar-open{overflow:hidden}

        /* Sidebar */
        .sidebar{width:var(--sidebar-w);background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;height:100vh;overflow-y:auto;z-index:100;transition:transform .3s cubic-bezier(.4,0,.2,1)}
        .sidebar-close-btn{display:none;position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;color:var(--t2);cursor:pointer;padding:.25rem;border-radius:6px;z-index:10}
        .sidebar-close-btn:hover{background:var(--bg3);color:var(--t1)}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:90;backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px)}
        .sidebar-brand{padding:1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.75rem}
        .sidebar-brand-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem}
        .sidebar-brand h2{font-size:1rem;font-weight:700}
        .sidebar-nav{padding:.75rem;flex:1}
        .nav-section{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:var(--t3);padding:.75rem .5rem .25rem;font-weight:600}
        .nav-link{display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border-radius:8px;color:var(--t2);font-size:.85rem;text-decoration:none;transition:all .15s;margin-bottom:2px}
        .nav-link:hover{background:var(--bg3);color:var(--t1)}
        .nav-link.active{background:rgba(99,102,241,.15);color:var(--accent2)}
        .nav-icon{width:18px;text-align:center}
        .sidebar-footer{padding:1rem;border-top:1px solid var(--border)}
        .user-info{display:flex;align-items:center;gap:.5rem}
        .user-avatar{width:32px;height:32px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:600;color:white}
        .user-name{font-size:.8rem;font-weight:600}.user-email{font-size:.7rem;color:var(--t3)}

        /* Hamburger Menu Button */
        .hamburger-btn{display:none;background:none;border:none;cursor:pointer;padding:.5rem;border-radius:8px;color:var(--t1);font-size:1.4rem;line-height:1;flex-shrink:0;transition:background .15s}
        .hamburger-btn:hover{background:var(--bg3)}

        /* Mobile Nav (Visible only on small screens) */
        .mobile-nav{display:none;overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap;background:var(--bg2);border-bottom:1px solid var(--border);padding:0 1rem;gap:1.5rem}
        .mobile-nav::-webkit-scrollbar{display:none} /* Hide scrollbar for clean look */
        .mobile-nav-link{display:inline-block;text-decoration:none;color:var(--t2);font-size:.85rem;font-weight:600;padding:.75rem 0;position:relative;transition:color .15s}
        .mobile-nav-link:hover{color:var(--t1)}
        .mobile-nav-link.active{color:var(--accent)}
        .mobile-nav-link.active::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:var(--accent);border-radius:2px 2px 0 0}

        /* Main content */
        .main{flex:1;margin-left:var(--sidebar-w);min-height:100vh}
        .topbar{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--bg2);gap:.75rem}
        .topbar h1{font-size:1.25rem;font-weight:700}
        .content{padding:1.5rem}
        /* Cards */
        .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
        .stat-card{background:var(--bg3);border:1px solid var(--border);border-radius:12px;padding:1.25rem}
        .stat-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--t3);margin-bottom:.25rem}
        .stat-value{font-size:1.75rem;font-weight:700}
        .stat-change{font-size:.75rem;margin-top:.25rem}
        /* Table */
        .table-card{background:var(--bg3);border:1px solid var(--border);border-radius:12px;overflow:hidden}
        .table-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem}
        .table-title{font-size:.9rem;font-weight:600}
        .table-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:.75rem 1.25rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:var(--t3);border-bottom:1px solid var(--border);font-weight:600;white-space:nowrap}
        td{padding:.75rem 1.25rem;border-bottom:1px solid rgba(0,0,0,.05);font-size:.85rem;color:var(--t2)}
        tr:hover td{background:rgba(0,0,0,.02)}
        /* Badges */
        .badge{display:inline-block;padding:.15rem .6rem;border-radius:20px;font-size:.7rem;font-weight:600}
        .badge-ok{background:rgba(16,185,129,.15);color:var(--ok)}.badge-err{background:rgba(239,68,68,.15);color:var(--err)}.badge-warn{background:rgba(245,158,11,.15);color:var(--warn)}.badge-info{background:rgba(59,130,246,.15);color:var(--info)}
        /* Buttons */
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:8px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s;font-family:inherit}
        .btn-primary{background:var(--accent);color:white}.btn-primary:hover{background:var(--accent2)}
        .btn-secondary{background:var(--bg2);color:var(--t2);border:1px solid var(--border)}.btn-secondary:hover{background:var(--bg3)}
        .btn-sm{padding:.35rem .7rem;font-size:.75rem}
        .btn-danger{background:rgba(239,68,68,.15);color:var(--err)}.btn-danger:hover{background:rgba(239,68,68,.25)}
        /* Form */
        .form-group{margin-bottom:1rem}.form-label{display:block;font-size:.75rem;font-weight:600;color:var(--t3);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em}
        .form-input{width:100%;padding:.6rem .85rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--t1);font-size:.85rem;font-family:inherit;outline:none;transition:border .2s}
        .form-input:focus{border-color:var(--accent)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        /* Alert */
        .alert{padding:.75rem 1rem;border-radius:8px;font-size:.8rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
        .alert-success{background:rgba(16,185,129,.1);color:var(--ok);border:1px solid rgba(16,185,129,.2)}
        .alert-danger{background:rgba(239,68,68,.1);color:var(--err);border:1px solid rgba(239,68,68,.2)}
        /* Pagination */
        .pagination{display:flex;gap:.25rem;margin-top:1rem;justify-content:center;flex-wrap:wrap}
        .pagination a,.pagination span{padding:.4rem .7rem;border-radius:6px;font-size:.8rem;text-decoration:none;color:var(--t2)}
        .pagination a:hover{background:var(--bg3)}.pagination .active span{background:var(--accent);color:white}

        /* ===== RESPONSIVE: Tablet (≤1024px) ===== */
        @media(max-width:1024px){
            .stat-grid{grid-template-columns:repeat(auto-fit,minmax(160px,1fr))}
        }

        /* ===== RESPONSIVE: Mobile (≤768px) ===== */
        @media(max-width:768px){
            .hamburger-btn{display:flex;align-items:center;justify-content:center}
            .mobile-nav{display:flex} /* Show mobile nav */
            .sidebar{transform:translateX(-100%);box-shadow:4px 0 24px rgba(0,0,0,.15)}
            .sidebar.open{transform:translateX(0)}
            .sidebar-close-btn{display:block}
            .sidebar-overlay.active{display:block}
            .main{margin-left:0}
            .topbar{padding:.75rem 1rem}
            .topbar h1{font-size:1.1rem}
            .content{padding:1rem}
            .form-row{grid-template-columns:1fr}
            .stat-grid{grid-template-columns:1fr 1fr}
            .stat-card{padding:1rem}
            .stat-value{font-size:1.4rem}
            td{padding:.5rem .75rem;font-size:.8rem}
            th{padding:.5rem .75rem}
        }

        /* ===== RESPONSIVE: Small Phone (≤480px) ===== */
        @media(max-width:480px){
            .topbar{padding:.5rem .75rem}
            .topbar h1{font-size:1rem}
            .content{padding:.75rem}
            .stat-grid{grid-template-columns:1fr 1fr;gap:.5rem}
            .stat-card{padding:.75rem;border-radius:8px}
            .stat-label{font-size:.65rem}
            .stat-value{font-size:1.2rem}
            .btn{padding:.35rem .7rem;font-size:.75rem}
            .table-header{padding:.75rem}
        }
    </style>
</head>
<body>
    {{-- Sidebar Overlay --}}
    <div class="sidebar-overlay" id="adminSidebarOverlay"></div>

    <aside class="sidebar" id="adminSidebar">
        <button class="sidebar-close-btn" id="adminSidebarCloseBtn" aria-label="Close menu">✕</button>
        <div class="sidebar-brand" style="justify-content: center;">
            <img src="{{ asset('images/logo.png') }}" alt="EdgeMail" style="height: 90px; width: auto; max-width: 200px; object-fit: contain;">
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span> Dashboard</a>

            <div class="nav-section">Management</div>
            <a href="{{ route('admin.domains.index') }}" class="nav-link {{ request()->routeIs('admin.domains.*') ? 'active' : '' }}"><span class="nav-icon">🌐</span> Domains</a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="nav-icon">👥</span> Users</a>
            <a href="{{ route('admin.mailboxes.index') }}" class="nav-link {{ request()->routeIs('admin.mailboxes.*') ? 'active' : '' }}"><span class="nav-icon">📬</span> Mailboxes</a>
            <a href="{{ route('admin.aliases.index') }}" class="nav-link {{ request()->routeIs('admin.aliases.*') ? 'active' : '' }}"><span class="nav-icon">🔀</span> Aliases</a>

            <div class="nav-section">Monitoring</div>
            <a href="{{ route('admin.logs.index') }}" class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}"><span class="nav-icon">📋</span> SMTP Logs</a>
            <a href="{{ route('admin.queue.index') }}" class="nav-link {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}"><span class="nav-icon">📤</span> Queue</a>
            <a href="{{ route('admin.spam.index') }}" class="nav-link {{ request()->routeIs('admin.spam.*') ? 'active' : '' }}"><span class="nav-icon">🛡️</span> Spam</a>
            <a href="{{ route('admin.storage.index') }}" class="nav-link {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}"><span class="nav-icon">💾</span> Storage</a>
            <a href="{{ route('admin.security.index') }}" class="nav-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}"><span class="nav-icon">🔒</span> Security</a>

            <div class="nav-section">Webmail</div>
            <a href="{{ route('webmail.inbox') }}" class="nav-link"><span class="nav-icon">📧</span> Open Webmail</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="user-email">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:.75rem">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" style="width:100%">Logout</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:.75rem">
                <button class="hamburger-btn" id="adminHamburgerBtn" aria-label="Open menu">☰</button>
                <h1>@yield('title', 'Dashboard')</h1>
            </div>
            <div>@yield('actions')</div>
        </div>
        
        <!-- Mobile Horizontal Navigation -->
        <nav class="mobile-nav">
            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.domains.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.domains.*') ? 'active' : '' }}">Domains</a>
            <a href="{{ route('admin.users.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('admin.mailboxes.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.mailboxes.*') ? 'active' : '' }}">Mailboxes</a>
            <a href="{{ route('admin.aliases.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.aliases.*') ? 'active' : '' }}">Aliases</a>
            <a href="{{ route('admin.logs.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">SMTP Logs</a>
            <a href="{{ route('admin.queue.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}">Queue</a>
            <a href="{{ route('admin.spam.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.spam.*') ? 'active' : '' }}">Spam</a>
            <a href="{{ route('admin.storage.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}">Storage</a>
            <a href="{{ route('admin.security.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">Security</a>
            <a href="{{ route('webmail.inbox') }}" class="mobile-nav-link">Webmail</a>
        </nav>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">⚠️ {{ $errors->first() }}</div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
    (function(){
        var hamburger = document.getElementById('adminHamburgerBtn');
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.getElementById('adminSidebarOverlay');
        var closeBtn = document.getElementById('adminSidebarCloseBtn');
        if(!hamburger || !sidebar) return;

        function openSidebar(){
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.classList.add('sidebar-open');
        }
        function closeSidebar(){
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }

        hamburger.addEventListener('click', openSidebar);
        if(overlay) overlay.addEventListener('click', closeSidebar);
        if(closeBtn) closeBtn.addEventListener('click', closeSidebar);

        // Close sidebar on nav link click (mobile UX)
        sidebar.querySelectorAll('.nav-link').forEach(function(link){
            link.addEventListener('click', function(){
                if(window.innerWidth <= 768) closeSidebar();
            });
        });
    })();
    </script>
</body>
</html>

