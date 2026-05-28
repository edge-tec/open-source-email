<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Webmail') - EdgeMail</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root{--bg:#f8fafc;--bg2:#ffffff;--border:#e2e8f0;--t1:#0f172a;--t2:#475569;--t3:#94a3b8;--accent:#3b82f6;--accent2:#2563eb;--ok:#10b981;--err:#ef4444;--warn:#f59e0b;--bg-hover:#f1f5f9;--bg-active:#e0e7ff;--shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)}
        @media (prefers-color-scheme: dark) {
            :root{--bg:#0f172a;--bg2:#1e293b;--border:#334155;--t1:#f8fafc;--t2:#cbd5e1;--t3:#64748b;--bg-hover:#334155;--bg-active:rgba(59,130,246,.2);--shadow:0 4px 6px -1px rgba(0,0,0,.3)}
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--t1);display:flex;height:100vh;overflow:hidden}

        /* Sidebar */
        .sidebar{width:240px;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column}
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

        /* Topbar & Search */
        .main{flex:1;display:flex;flex-direction:column;min-width:0}
        .topbar{height:60px;background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem}
        .search-bar{display:flex;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:0 .75rem;width:400px}
        .search-input{border:none;background:transparent;padding:.6rem;width:100%;outline:none;color:var(--t1);font-family:inherit}
        .user-menu{display:flex;align-items:center;gap:1rem}
        .user-email{font-size:.85rem;color:var(--t2);font-weight:500}

        /* Common Elements */
        .content{flex:1;overflow:hidden;position:relative;background:var(--bg)}
        .btn{padding:.5rem 1rem;border-radius:6px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;text-decoration:none;font-family:inherit}
        .btn-primary{background:var(--accent);color:white}.btn-primary:hover{background:var(--accent2)}
        .btn-secondary{background:var(--bg);border:1px solid var(--border);color:var(--t1)}.btn-secondary:hover{background:var(--bg-hover)}
        .btn-icon{background:transparent;border:none;color:var(--t2);cursor:pointer;padding:.5rem;border-radius:6px}.btn-icon:hover{background:var(--bg-hover);color:var(--t1)}
        
        .alert{padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1rem;background:var(--bg2);border:1px solid var(--border)}
        .alert-success{border-left:4px solid var(--ok)}.alert-error{border-left:4px solid var(--err)}

        /* Form */
        .form-group{margin-bottom:1rem}
        .form-label{display:block;font-size:.8rem;font-weight:600;color:var(--t2);margin-bottom:.4rem}
        .form-control{width:100%;padding:.6rem;border:1px solid var(--border);border-radius:6px;background:var(--bg);color:var(--t1);font-family:inherit;font-size:.9rem}
        .form-control:focus{outline:none;border-color:var(--accent)}
    </style>
    @yield('styles')
</head>
<body>
    @if(isset($mailbox))
    <aside class="sidebar">
        <div class="brand" style="justify-content: center;">
            <img src="{{ asset('images/logo.png') }}" alt="EdgeMail" style="height: auto; max-width: 160px; max-height: 50px;">
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
            <form action="{{ route('webmail.search') }}" method="GET" class="search-bar">
                <span>🔍</span>
                <input type="text" name="q" class="search-input" placeholder="Search mail..." value="{{ request('q') }}">
            </form>
            <div class="user-menu">
                <span class="user-email">{{ $mailbox->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding:.4rem .75rem;font-size:.8rem">Logout</button>
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
    
    @yield('scripts')
</body>
</html>
