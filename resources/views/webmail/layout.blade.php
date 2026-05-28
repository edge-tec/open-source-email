<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Webmail') - EdgeMail</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root{--bg:#f8fafc;--bg2:#ffffff;--border:#e2e8f0;--t1:#0f172a;--t2:#475569;--t3:#94a3b8;--accent:#3b82f6;--accent2:#2563eb;--ok:#10b981;--err:#ef4444;--warn:#f59e0b;--bg-hover:#f1f5f9;--bg-active:#e0e7ff;--shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06);--sidebar-w:240px}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--t1);display:flex;height:100vh;overflow:hidden}
        body.sidebar-open{overflow:hidden}

        /* Sidebar */
        .sidebar{width:var(--sidebar-w);background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;height:100vh;z-index:100;transition:transform .3s cubic-bezier(.4,0,.2,1)}
        .sidebar-close-btn{display:none;position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;color:var(--t2);cursor:pointer;padding:.25rem;border-radius:6px;z-index:10}
        .sidebar-close-btn:hover{background:var(--bg-hover);color:var(--t1)}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:90;backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px)}
        .brand{padding:1rem 1.25rem;display:flex;align-items:center;gap:.75rem;border-bottom:1px solid var(--border)}
        .brand-icon{width:32px;height:32px;background:var(--accent);color:white;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:bold}
        .compose-btn-wrap{padding:1.25rem}
        .btn-compose{display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.75rem;background:var(--accent);color:white;border-radius:8px;text-decoration:none;font-weight:600;box-shadow:var(--shadow);transition:background .2s}
        .btn-compose:hover{background:var(--accent2)}
        .nav{flex:1;overflow-y:auto;padding:0 .75rem}
        .nav-link{display:flex;align-items:center;justify-content:space-between;padding:.6rem .75rem;border-radius:6px;color:var(--t2);text-decoration:none;margin-bottom:2px;font-size:.9rem}
        .nav-link:hover{background:var(--bg-hover);color:var(--t1)}
        .nav-link.active{background:var(--bg-active);color:var(--accent);font-weight:600}
        .nav-link-icon{display:flex;align-items:center;gap:.75rem}
        .badge{background:var(--accent);color:white;padding:.1rem .4rem;border-radius:10px;font-size:.7rem;font-weight:600}

        /* Hamburger Menu Button */
        .hamburger-btn{display:none;background:none;border:none;cursor:pointer;padding:.5rem;border-radius:8px;color:var(--t1);font-size:1.4rem;line-height:1;flex-shrink:0;transition:background .15s}
        .hamburger-btn:hover{background:var(--bg-hover)}

        /* Topbar & Search */
        .main{flex:1;display:flex;flex-direction:column;min-width:0}
        .topbar{height:60px;background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;gap:.75rem}
        .search-bar{display:flex;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:0 .75rem;width:400px;flex-shrink:1;min-width:0}
        .search-input{border:none;background:transparent;padding:.6rem;width:100%;outline:none;color:var(--t1);font-family:inherit}
        .user-menu{display:flex;align-items:center;gap:1rem;flex-shrink:0}
        .user-email{font-size:.85rem;color:var(--t2);font-weight:500}

        /* Common Elements */
        .content{flex:1;overflow:hidden;position:relative;background:var(--bg)}
        .btn{padding:.5rem 1rem;border-radius:6px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;text-decoration:none;font-family:inherit}
        .btn-primary{background:var(--accent);color:white}.btn-primary:hover{background:var(--accent2)}
        .btn-secondary{background:var(--bg);border:1px solid var(--border);color:var(--t1)}.btn-secondary:hover{background:var(--bg-hover)}
        .btn-logout{background:#fee2e2;color:#ef4444;border:1px solid #fca5a5;transition:all 0.2s}.btn-logout:hover{background:#fecaca}
        .btn-icon{background:transparent;border:none;color:var(--t2);cursor:pointer;padding:.5rem;border-radius:6px}.btn-icon:hover{background:var(--bg-hover);color:var(--t1)}
        
        .alert{padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1rem;background:var(--bg2);border:1px solid var(--border)}
        .alert-success{border-left:4px solid var(--ok)}.alert-error{border-left:4px solid var(--err)}

        /* Form */
        .form-group{margin-bottom:1rem}
        .form-label{display:block;font-size:.8rem;font-weight:600;color:var(--t2);margin-bottom:.4rem}
        .form-control{width:100%;padding:.6rem;border:1px solid var(--border);border-radius:6px;background:var(--bg);color:var(--t1);font-family:inherit;font-size:.9rem}
        .form-control:focus{outline:none;border-color:var(--accent)}

        /* ===== RESPONSIVE: Tablet (≤1024px) ===== */
        @media(max-width:1024px){
            .search-bar{width:280px}
            .user-email{display:none}
        }

        /* ===== RESPONSIVE: Mobile & Small Tablet (≤768px) ===== */
        @media(max-width:768px){
            .hamburger-btn{display:flex;align-items:center;justify-content:center}
            .sidebar{position:fixed;top:0;left:0;height:100vh;transform:translateX(-100%);box-shadow:4px 0 24px rgba(0,0,0,.15)}
            .sidebar.open{transform:translateX(0)}
            .sidebar-close-btn{display:block}
            .sidebar-overlay.active{display:block}
            .topbar{padding:0 1rem;height:56px}
            .search-bar{width:100%;flex:1}
            .user-email{display:none}
            .content{overflow-y:auto}
        }

        /* ===== RESPONSIVE: Small Phone (≤480px) ===== */
        @media(max-width:480px){
            .topbar{padding:0 .75rem;gap:.5rem}
            .search-bar{padding:0 .5rem}
            .search-input{padding:.5rem;font-size:.85rem}
            .btn{padding:.4rem .75rem;font-size:.8rem}
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(isset($mailbox))
    {{-- Sidebar Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">✕</button>
        <div class="brand" style="justify-content: center;">
            <img src="{{ asset('images/logo.png') }}" alt="EdgeMail" style="height: 90px; width: auto; max-width: 200px; object-fit: contain;">
        </div>
        <div class="compose-btn-wrap">
            <a href="{{ route('webmail.compose') }}" class="btn-compose">✏️ Compose</a>
        </div>
        <nav class="nav">
            <a href="{{ route('webmail.inbox') }}" class="nav-link {{ request()->routeIs('webmail.inbox') && !isset($currentFolder) ? 'active' : '' }}">
                <span class="nav-link-icon">📥 Inbox</span>
                @if(isset($unread) && $unread > 0)<span class="badge">{{ $unread }}</span>@endif
            </a>
            
            <div style="margin:1.5rem 0 .5rem;font-size:.7rem;text-transform:uppercase;color:var(--t3);font-weight:600;padding:0 .75rem">Folders</div>
            @if(isset($folders))
                @foreach($folders as $f)
                    @if(strtoupper($f) !== 'INBOX')
                        @php
                            $icon = '📁';
                            $lower = strtolower($f);
                            if ($lower === 'sent') $icon = '📤';
                            elseif ($lower === 'drafts') $icon = '📝';
                            elseif ($lower === 'junk') $icon = '🚫';
                            elseif ($lower === 'trash') $icon = '🗑️';
                            elseif ($lower === 'promotions') $icon = '🏷️';
                            elseif ($lower === 'social') $icon = '👥';
                            elseif ($lower === 'updates' || $lower === 'update') $icon = '🔔';
                        @endphp
                        <a href="{{ route('webmail.folder', urlencode($f)) }}" class="nav-link {{ isset($currentFolder) && $currentFolder === $f ? 'active' : '' }}">
                            <span class="nav-link-icon">{{ $icon }} {{ $f }}</span>
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>
        
        <div style="padding:1rem;border-top:1px solid var(--border)">
            <a href="{{ route('webmail.settings') }}" class="nav-link {{ request()->routeIs('webmail.settings') ? 'active' : '' }}" style="padding:.5rem">
                <span class="nav-link-icon">⚙️ Settings</span>
            </a>
            @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="nav-link" style="padding:.5rem">
                <span class="nav-link-icon">🛡️ Admin Panel</span>
            </a>
            @endif
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu">☰</button>
            <form action="{{ route('webmail.search') }}" method="GET" class="search-bar">
                <span>🔍</span>
                <input type="text" name="q" class="search-input" placeholder="Search mail..." value="{{ request('q') }}">
            </form>
            <div class="user-menu">
                <span class="user-email">{{ $mailbox->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-logout" style="padding:.4rem .75rem;font-size:.8rem">Logout</button>
                </form>
            </div>
        </header>
        <div class="content">
            @yield('content')
        </div>
    </main>
    @else
        @yield('content')
    @endif
    
    <script>
    (function(){
        var hamburger = document.getElementById('hamburgerBtn');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var closeBtn = document.getElementById('sidebarCloseBtn');
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
        sidebar.querySelectorAll('.nav-link, .btn-compose').forEach(function(link){
            link.addEventListener('click', function(){
                if(window.innerWidth <= 768) closeSidebar();
            });
        });
    })();
    </script>
    @yield('scripts')
</body>
</html>
