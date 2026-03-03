<!DOCTYPE html>
<html lang="pt-BR" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Central 3D') — {{ \App\Models\Setting::get('company_name', 'ERP Impressão 3D') }}</title>
    @if(auth()->check() && auth()->user()->store_logo)
        <link rel="icon" href="{{ auth()->user()->store_logo_thumbnail_url }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 min-h-screen flex text-sm sm:text-base">

    {{-- Overlay for mobile sidebar --}}
    @auth
    <div id="sidebarOverlay" class="fixed inset-0 bg-zinc-950/50 dark:bg-zinc-950/80 z-40 hidden md:hidden transition-opacity opacity-0" aria-hidden="true"></div>
    @endauth

    {{-- Sidebar --}}
    @auth
    @if(!View::hasSection('hide_sidebar'))
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 transition-transform -translate-x-full md:translate-x-0 md:static md:flex-shrink-0 flex flex-col">
        <div class="h-16 flex items-center justify-between px-6 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                @if(auth()->check() && auth()->user()->store_logo)
                    <img src="{{ auth()->user()->store_logo_thumbnail_url }}" alt="Logo" class="w-7 h-7 rounded object-cover">
                @else
                    <i class="fas fa-cube text-xl text-zinc-900 dark:text-zinc-100"></i>
                @endif
                <span class="font-bold text-zinc-900 dark:text-white truncate" style="max-width: 140px;">
                    {{ auth()->check() ? (auth()->user()->store_name ?? 'ERP 3D') : 'Central 3D' }}
                </span>
            </div>
            <button id="closeSidebar" class="md:hidden text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 p-2">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('dashboard') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('products.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-boxes-stacked w-5 text-center"></i>
                <span>Produtos</span>
            </a>
            <a href="{{ route('filaments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('filaments.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-fill-drip w-5 text-center"></i>
                <span>Filamentos</span>
            </a>
            <a href="{{ route('stock.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('stock.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-warehouse w-5 text-center"></i>
                <span>Estoque</span>
            </a>
            <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('sales.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-cash-register w-5 text-center"></i>
                <span>Vendas</span>
            </a>
            <a href="{{ route('costs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('costs.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-calculator w-5 text-center"></i>
                <span>Custos 3D</span>
            </a>
            <a href="{{ route('nfe.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('nfe.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-file-invoice w-5 text-center"></i>
                <span>Notas Fiscais</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('calendar.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-calendar-alt w-5 text-center"></i>
                <span>Calendário</span>
            </a>

            @if(auth()->user()->is_admin)
                <div class="my-4 border-t border-zinc-200 dark:border-zinc-800/50"></div>
                <a href="{{ route('offers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('offers.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                    <i class="fas fa-tags w-5 text-center"></i>
                    <span>Ofertas</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('admin.users.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                    <i class="fas fa-users-cog w-5 text-center"></i>
                    <span>Gerenciar Usuários</span>
                </a>
            @endif

            <div class="my-4 border-t border-zinc-200 dark:border-zinc-800/50"></div>
            
            <a href="{{ route('modeler-requests.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white border-l-2 {{ request()->routeIs('modeler-requests.index') ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold' : 'border-transparent' }}">
                <i class="fas fa-magic w-5 text-center text-emerald-500"></i>
                <span>Pedidos de Modelagem</span>
            </a>

            <div class="my-4 border-t border-zinc-200 dark:border-zinc-800/50"></div>

            <a href="{{ route('store.show', auth()->user()->slug) }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                <i class="fas fa-store w-5 text-center"></i>
                <span>Minha Loja</span>
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('settings.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-cog w-5 text-center"></i>
                <span>Configurações</span>
            </a>
            
            <a href="{{ route('plans.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('plans.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-star w-5 text-center text-amber-500"></i>
                <span>Meu Plano</span>
                @php $userPlan = auth()->user()->currentPlan(); @endphp
                @if($userPlan && $userPlan->slug === 'free')
                    <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-gradient-to-r from-indigo-500 to-purple-600 text-white uppercase tracking-wider">Upgrade</span>
                @elseif($userPlan)
                    <span class="ml-auto text-[10px] font-medium px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400">{{ $userPlan->name }}</span>
                @endif
            </a>
            
            <a href="{{ route('wishlists.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white {{ request()->routeIs('wishlists.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium' : '' }}">
                <i class="fas fa-heart w-5 text-center text-pink-500"></i>
                <span>Lista de Desejos</span>
            </a>
        </nav>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-3 px-3 py-2 mb-2">
                <i class="fas fa-user-circle text-zinc-400 w-5 text-center text-lg"></i>
                <span class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white text-left transition-colors">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>
    @endif
    @endauth

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col min-w-0 bg-transparent">
        @if(!View::hasSection('hide_header'))
        <header class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 md:bg-transparent md:dark:bg-transparent sticky top-0 z-30">
            <div class="flex items-center gap-4">
                @auth
                <button id="openSidebar" class="md:hidden p-2 -ml-2 text-zinc-500 hover:text-zinc-900 dark:hover:text-white rounded-md">
                    <i class="fas fa-bars"></i>
                </button>
                @endauth
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-white truncate">@yield('page-title', 'Dashboard')</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <button id="themeToggle" class="p-2 text-zinc-500 hover:text-zinc-900 dark:hover:text-white rounded-md transition-colors" title="Alternar tema">
                    <i class="fas fa-sun" id="themeIcon"></i>
                </button>
                @yield('top-actions')
            </div>
        </header>
        @endif

        <div class="p-4 sm:p-6 lg:p-8 flex-1 overflow-x-hidden">
            @if(session('success'))
                <div id="successAlert" class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-lg border border-emerald-200 dark:border-emerald-500/20 shadow-sm">
                    <i class="fas fa-check-circle flex-shrink-0 text-emerald-500 dark:text-emerald-400"></i>
                    <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
                    <button class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 flex gap-3 p-4 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-500/20 shadow-sm">
                    <i class="fas fa-exclamation-circle flex-shrink-0 mt-0.5 text-red-500 dark:text-red-400"></i>
                    <div class="flex-1 text-sm font-medium">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        // Sidebar Mobile Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function openSidebar() {
            if(!sidebar) return;
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarFunc() {
            if(!sidebar) return;
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebarFunc);
        if (overlay) overlay.addEventListener('click', closeSidebarFunc);

        // Auto-dismiss alerts
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-10px)';
                successAlert.style.transition = 'all 0.3s ease';
                setTimeout(() => successAlert.remove(), 300);
            }, 4000);
        }

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            if (themeIcon) {
                themeIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
            }
        }
        
        updateThemeIcon();

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateThemeIcon();
            });
        }
    </script>
    
    @yield('scripts')
    @stack('image-crop-scripts')

</body>
</html>
