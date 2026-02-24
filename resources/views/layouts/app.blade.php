<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<script>document.documentElement.dataset.theme=localStorage.getItem('theme')||'dark';</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP 3D Print') — {{ \App\Models\Setting::get('company_name', 'ERP Impressão 3D') }}</title>
    @if(auth()->check() && auth()->user()->store_logo)
        <link rel="icon" href="{{ asset('storage/' . auth()->user()->store_logo) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @php
        $cp = auth()->user()->store_color_primary ?? '#16a34a';
        $ca = auth()->user()->store_color_accent ?? '#86efac';
    @endphp
    <style>
        :root {
            --primary: {{ $cp }};
            --primary-light: {{ $cp }}dd;
            --primary-dark: {{ $cp }}bb;
            --primary-glow: {{ $cp }}33;
            --accent: {{ $ca }};
            --accent-light: {{ $ca }}dd;
            --border-focus: {{ $cp }};
        }
    </style>
</head>
<body>
    <div class="app-container">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    @if(auth()->user()->store_logo)
                        <img src="{{ asset('storage/' . auth()->user()->store_logo) }}" alt="" style="width:28px; height:28px; border-radius:6px; object-fit:cover;">
                    @else
                        <i class="fas fa-cube logo-icon"></i>
                    @endif
                    <span class="logo-text">{{ auth()->user()->store_name ?? 'ERP 3D' }}</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Produtos</span>
                </a>
                <a href="{{ route('filaments.index') }}" class="nav-item {{ request()->routeIs('filaments.*') ? 'active' : '' }}">
                    <i class="fas fa-fill-drip"></i>
                    <span>Filamentos</span>
                </a>
                <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Estoque</span>
                </a>
                <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                    <i class="fas fa-cash-register"></i>
                    <span>Vendas</span>
                </a>
                <a href="{{ route('costs.index') }}" class="nav-item {{ request()->routeIs('costs.*') ? 'active' : '' }}">
                    <i class="fas fa-calculator"></i>
                    <span>Custos 3D</span>
                </a>
                <a href="{{ route('calendar.index') }}" class="nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendário</span>
                </a>

                @if(auth()->user()->is_admin)
                    <div class="nav-divider"></div>
                    <a href="{{ route('offers.index') }}" class="nav-item {{ request()->routeIs('offers.*') ? 'active' : '' }}">
                        <i class="fas fa-tags" style="color:#ff6b35;"></i>
                        <span>Ofertas</span>
                    </a>
                @endif

                <div class="nav-divider"></div>

                <a href="{{ route('store.show', auth()->user()->slug) }}" class="nav-item" target="_blank">
                    <i class="fas fa-store"></i>
                    <span>Minha Loja</span>
                </a>
                <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div style="display:flex; align-items:center; gap:8px; padding:0 var(--space-sm); margin-bottom:var(--space-sm);">
                    <i class="fas fa-user-circle" style="color:var(--text-muted);"></i>
                    <span style="font-size:0.78rem; color:var(--text-secondary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item" style="width:100%; border:none; background:none; cursor:pointer; text-align:left; color:var(--text-muted);">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            <header class="top-bar">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                <div class="top-bar-actions">
                    <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                        <i class="fas fa-sun" id="themeIcon"></i>
                    </button>
                    @yield('top-actions')
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success" id="successAlert">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="content-area">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Overlay for mobile sidebar --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('visible');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('visible');
            });
        }

        // Auto-dismiss success alerts
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-10px)';
                setTimeout(() => successAlert.remove(), 300);
            }, 4000);
        }
    </script>

    @yield('scripts')
    @stack('image-crop-scripts')

    <script>
        // Theme toggle
        (function() {
            const toggle = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            function applyIcon() {
                const t = document.documentElement.dataset.theme;
                icon.className = t === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
            applyIcon();
            toggle.addEventListener('click', function() {
                const current = document.documentElement.dataset.theme;
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = next;
                localStorage.setItem('theme', next);
                applyIcon();
            });
        })();
    </script>
</body>
</html>
