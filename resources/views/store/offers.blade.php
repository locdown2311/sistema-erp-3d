<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofertas — {{ $store->store_name }}</title>
    @if($store->store_logo)
        <link rel="icon" href="{{ asset('storage/' . $store->store_logo) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --store-primary: {{ $store->store_color_primary ?? '#10b981' }};
            --store-accent: {{ $store->store_color_accent ?? '#34d399' }};
        }
        
        .bg-store-primary { background-color: var(--store-primary); }
        .text-store-primary { color: var(--store-primary); }
        
        .store-header-bg {
            background: linear-gradient(135deg, color-mix(in srgb, var(--store-primary) 15%, transparent), color-mix(in srgb, var(--store-accent) 5%, transparent));
        }
        .offer-card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(239, 68, 68, 0.4);
            border-color: rgba(239, 68, 68, 0.3);
        }
        .offer-img-bg {
            background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(255,68,68,0.05));
        }
        .store-nav-active {
            background-color: var(--store-primary) !important;
            color: white !important;
        }
    </style>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col font-sans antialiased selection:bg-orange-500/30">
    <header class="store-header-bg border-b border-zinc-200/50 dark:border-zinc-800/50 pt-8 sm:pt-12 pb-6 px-6 sm:px-8 shadow-sm">
        <div class="max-w-6xl mx-auto w-full">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 sm:gap-8">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden flex-shrink-0 border-4 border-white dark:border-zinc-900 bg-white dark:bg-zinc-800 shadow-xl flex items-center justify-center transform hover:-rotate-3 transition-transform duration-300">
                    @if($store->store_logo)
                        <img src="{{ asset('storage/' . $store->store_logo) }}" alt="{{ $store->store_name }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-store text-4xl text-zinc-300 dark:text-zinc-600"></i>
                    @endif
                </div>
                
                <div class="flex-1 text-center sm:text-left mb-2 sm:mb-0">
                    <h1 class="text-3xl sm:text-4xl font-bold text-zinc-900 dark:text-white mb-2 tracking-tight">{{ $store->store_name }}</h1>
                    @if($store->description)
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm max-w-xl mx-auto sm:mx-0">{{ $store->description }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <button id="themeToggleStore" class="w-10 h-10 rounded-xl bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm border border-zinc-200/50 dark:border-zinc-700/50 text-zinc-700 dark:text-zinc-300 flex items-center justify-center hover:bg-white dark:hover:bg-zinc-800 transition-all shadow-sm" title="Alternar tema">
                        <i class="fas fa-sun" id="themeIconStore"></i>
                    </button>
                    @auth
                        @if(auth()->id() === $store->id)
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold rounded-xl hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-all shadow-md">
                                <i class="fas fa-tachometer-alt"></i> Painel
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <nav class="flex items-center justify-center sm:justify-start gap-2 mt-8 sm:mt-10 mb-[-24px] overflow-x-auto no-scrollbar pb-2">
                <a href="{{ route('store.show', $store->slug) }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-white whitespace-nowrap transition-all">
                    <i class="fas fa-box-open mr-2"></i> Produtos
                </a>
                <a href="{{ route('store.offers', $store->slug) }}" class="store-nav-active px-5 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap transition-all shadow-md">
                    🔥 Ofertas
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1 w-full max-w-6xl mx-auto px-6 sm:px-8 py-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center text-orange-500">
                    <i class="fas fa-fire"></i>
                </div>
                Ofertas Imperdíveis <span class="text-sm font-medium px-2.5 py-0.5 rounded-full bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 mb-0.5">{{ $offers->count() }}</span>
            </h2>
        </div>

        @if($offers->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
                @foreach($offers as $offer)
                    <div class="group bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm transition-all duration-300 offer-card-hover flex flex-col h-full relative">
                        
                        @if($offer->discount_percent)
                            <div class="absolute top-4 right-4 z-10">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-black rounded-full shadow-lg text-white bg-gradient-to-r from-orange-500 to-red-500">
                                    -{{ $offer->discount_percent }}% OFF
                                </span>
                            </div>
                        @endif

                        <div class="offer-img-bg relative aspect-[4/3] w-full flex items-center justify-center overflow-hidden p-6">
                            @if($offer->image_path)
                                <img src="{{ asset('storage/' . $offer->image_path) }}" alt="{{ $offer->name }}" class="w-full h-full object-contain filter drop-shadow-md group-hover:scale-110 transition-transform duration-500">
                            @else
                                <i class="fas fa-tag text-5xl text-orange-500 opacity-30 group-hover:scale-110 transition-transform duration-500"></i>
                            @endif
                        </div>
                        
                        <div class="p-5 sm:p-6 flex flex-col flex-1">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight mb-1 group-hover:text-orange-500 transition-colors">{{ $offer->name }}</h3>
                            
                            <div class="text-xs font-medium text-orange-600 dark:text-orange-400 uppercase tracking-wider mb-2">
                                {{ $offer->category }}
                            </div>
                            
                            @if($offer->description)
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-6 leading-relaxed">{{ $offer->description }}</p>
                            @else
                                <div class="mb-6"></div>
                            @endif
                            
                            <div class="mt-auto flex items-end justify-between pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                <div class="flex flex-col">
                                    <span class="text-xl font-black bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500 dark:from-emerald-400 dark:to-teal-300 leading-none mb-1">
                                        R$ {{ number_format($offer->price, 2, ',', '.') }}
                                    </span>
                                    @if($offer->original_price)
                                        <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500 line-through">
                                            R$ {{ number_format($offer->original_price, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                                
                                <a href="{{ $offer->affiliate_url }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white text-sm font-semibold rounded-xl hover:from-orange-600 hover:to-red-600 transition-all shadow-md hover:shadow-lg hover:shadow-orange-500/20">
                                    <i class="fas fa-shopping-cart"></i> Comprar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl p-16 text-center max-w-2xl mx-auto mt-12 shadow-sm">
                <div class="w-24 h-24 rounded-full offer-img-bg flex items-center justify-center mx-auto mb-6 text-orange-500 text-opacity-50 text-4xl">
                    <i class="fas fa-tags"></i>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">Nenhuma oferta no momento</h3>
                <p class="text-zinc-500 dark:text-zinc-400 text-base">Atualmente não temos ofertas especiais ativas. Fique de olho, pois adicionamos descontos regularmente!</p>
            </div>
        @endif
    </main>

    @include('store.partials.offer-alert')

    <script>
        // Init theme if needed
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        const storeThemeToggle = document.getElementById('themeToggleStore');
        const storeThemeIcon = document.getElementById('themeIconStore');
        
        function updateStoreThemeIcon() {
            if(!storeThemeIcon) return;
            const isDark = document.documentElement.classList.contains('dark');
            storeThemeIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
        }
        
        updateStoreThemeIcon();

        if (storeThemeToggle) {
            storeThemeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateStoreThemeIcon();
            });
        }
    </script>
</body>
</html>
