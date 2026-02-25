@extends('layouts.app')

@section('page-title', 'Central3D — O Marketplace da Impressão 3D')

@section('hide_sidebar', true)
@section('hide_header', true)

@section('content')

<!-- Home Page Wrapper to fix background colors since app.blade.php sets it on body but we want specific rendering here -->
<div class="min-h-screen bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 -mt-8 sm:-mt-12 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 pb-12">

<!-- Public Navbar -->
<div class="flex flex-col sm:flex-row items-center justify-between mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800 gap-4">
    <div class="flex items-center gap-2 text-2xl font-black text-emerald-600 dark:text-emerald-500">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
            <i class="fas fa-cube"></i>
        </div>
        Central3D
    </div>
    <div class="flex items-center gap-4">
        <button id="themeToggleHome" class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors shadow-sm" title="Alternar tema">
            <i class="fas fa-sun" id="themeIconHome"></i>
        </button>
        @auth
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-xl hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-all shadow-md">
                <i class="fas fa-tachometer-alt"></i> Meu Painel
            </a>
        @else
            <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors">Entrar</a>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-700 hover:to-teal-600 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5">
                Criar Loja Grátis
            </a>
        @endauth
    </div>
</div>

<!-- Hero Section -->
<div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-8 sm:p-16 mb-16 text-center shadow-xl shadow-emerald-900/10">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xNSkiLz48L3N2Zz4=')] opacity-50"></div>
    <div class="relative z-10 max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white mb-6 leading-tight tracking-tight">
            O Hub das Melhores Lojas de <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-200 to-teal-100">Impressão 3D</span>
        </h1>
        <p class="text-emerald-50 text-base sm:text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
            Encontre peças exclusivas, action figures e utilidades impressas pelas melhores lojas selecionadas pela comunidade.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="#lojas" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-white text-emerald-700 text-sm font-bold rounded-xl hover:bg-emerald-50 transition-colors shadow-lg shadow-black/10">
                Explorar Lojas
            </a>
            <a href="#ofertas" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-transparent border-2 border-emerald-400/50 text-white text-sm font-bold rounded-xl hover:bg-emerald-500/20 hover:border-emerald-400 transition-colors">
                Ver Ofertas Globais
            </a>
        </div>
    </div>
</div>

<!-- Top Stores -->
<div id="lojas" class="mb-20 scroll-mt-24">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/10 flex items-center justify-center text-orange-500">
            <i class="fas fa-fire text-lg"></i>
        </div>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Top Lojas da Comunidade</h2>
    </div>

    @if($topStores->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($topStores as $store)
                <a href="{{ route('store.show', $store->slug) }}" class="group flex flex-col items-center p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm hover:border-emerald-500 dark:hover:border-emerald-500/50 hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all">
                    <div class="w-20 h-20 rounded-full border-4 border-white dark:border-zinc-950 shadow-md mb-4 overflow-hidden flex items-center justify-center bg-zinc-50 dark:bg-zinc-800">
                        @if($store->store_logo)
                            <img src="{{ asset('storage/' . $store->store_logo) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $store->store_name }}">
                        @else
                            <i class="fas fa-store text-2xl text-zinc-400 dark:text-zinc-600 group-hover:scale-110 transition-transform duration-500"></i>
                        @endif
                    </div>
                    <h3 class="font-bold text-zinc-900 dark:text-white text-center text-lg mb-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $store->store_name }}</h3>
                    <div class="flex items-center gap-1.5 text-sm font-semibold text-orange-500 bg-orange-50 dark:bg-orange-500/10 px-3 py-1 rounded-full">
                        <i class="fas fa-arrow-up"></i> {{ number_format($store->upvotes ?? 0) }}
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-zinc-900/50 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-3xl text-center">
            <div class="w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-4">
                <i class="fas fa-store-slash text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">Nenhuma loja em destaque no momento</h3>
            <p class="text-zinc-500 dark:text-zinc-400">Crie sua loja e seja o primeiro do ranking mundial!</p>
        </div>
    @endif
</div>

<!-- Global Offers -->
<div id="ofertas" class="mb-10 scroll-mt-24">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-500">
            <i class="fas fa-tags text-lg"></i>
        </div>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Ofertas Globais em Destaque</h2>
    </div>

    @if($latestOffers->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($latestOffers as $offer)
                <div class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-zinc-200/50 dark:hover:shadow-black/50 hover:-translate-y-1 transition-all overflow-hidden">
                    <div class="relative aspect-video bg-zinc-100 dark:bg-zinc-950 flex items-center justify-center overflow-hidden p-6 z-0">
                        @if($offer->image_path)
                            <img src="{{ asset('storage/' . $offer->image_path) }}" class="w-full h-full object-contain filter drop-shadow-md group-hover:scale-110 transition-transform duration-500" alt="{{ $offer->name }}">
                        @else
                            <i class="fas fa-box text-5xl text-zinc-300 dark:text-zinc-700 group-hover:scale-110 transition-transform duration-500"></i>
                        @endif
                        
                        @if($offer->discount_percent)
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-2.5 py-1 rounded-lg font-bold text-xs shadow-md">
                                -{{ $offer->discount_percent }}%
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-6 flex flex-col flex-1 relative z-10 bg-white dark:bg-zinc-900">
                        @php
                            $owner = \App\Models\User::find($offer->user_id) ?? \App\Models\User::where('is_admin', true)->first();
                        @endphp
                        
                        @if($owner)
                            <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-2 flex items-center gap-1.5 uppercase tracking-wider">
                                <i class="fas fa-store"></i> {{ $owner->store_name ?? 'Central3D' }}
                            </div>
                        @endif
                        
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 leading-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $offer->name }}</h3>
                        
                        <div class="mt-auto flex flex-wrap items-baseline gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800 mb-5">
                            <span class="text-2xl font-black text-zinc-900 dark:text-white">R$ {{ number_format($offer->price, 2, ',', '.') }}</span>
                            @if($offer->original_price && $offer->original_price > $offer->price)
                                <span class="text-sm font-medium text-zinc-400 line-through">R$ {{ number_format($offer->original_price, 2, ',', '.') }}</span>
                            @endif
                        </div>
                        
                        <a href="{{ $offer->affiliate_url }}" target="_blank" class="block w-full text-center px-4 py-3 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-xl hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-sm">
                            Pegar Oferta <i class="fas fa-external-link-alt ml-1.5 text-xs opacity-70"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-zinc-900/50 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-3xl text-center">
            <div class="w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-4">
                <i class="fas fa-tag text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">Nenhuma oferta disponível no momento</h3>
            <p class="text-zinc-500 dark:text-zinc-400">Volte mais tarde para encontrar descontos incríveis!</p>
        </div>
    @endif
</div>

</div>
@endsection

@section('scripts')
<script>
    // Specific Theme Toggle for Home Page (since main header is hidden)
    const homeThemeToggle = document.getElementById('themeToggleHome');
    const homeThemeIcon = document.getElementById('themeIconHome');
    
    function updateHomeThemeIcon() {
        if(!homeThemeIcon) return;
        const isDark = document.documentElement.classList.contains('dark');
        homeThemeIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    updateHomeThemeIcon();

    if (homeThemeToggle) {
        homeThemeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateHomeThemeIcon();
            
            // Sync with sidebar toggle if it exists in DOM
            const globalIcon = document.getElementById('themeIcon');
            if(globalIcon) {
                globalIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
            }
        });
    }
</script>
@endsection
