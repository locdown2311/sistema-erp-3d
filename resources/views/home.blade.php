@extends('layouts.app')

@section('page-title', 'Central3D — O Marketplace da Impressão 3D')

@section('hide_sidebar', true)
@section('hide_header', true)

@section('content')

<!-- Home Page Wrapper -->
<div class="min-h-screen bg-zinc-50 dark:bg-[#09090b] text-zinc-900 dark:text-zinc-100 -mt-4 sm:-mt-6 lg:-mt-8 -mx-4 sm:-mx-6 lg:-mx-8 relative overflow-x-hidden transition-colors duration-300">

    <!-- Ambient Background Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-screen-xl h-full overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-emerald-500/20 dark:bg-emerald-500/10 blur-[100px] mix-blend-multiply dark:mix-blend-screen animate-pulse md:w-[800px] md:h-[800px]" style="animation-duration: 8s;"></div>
        <div class="absolute top-[20%] right-[-10%] w-[400px] h-[400px] rounded-full bg-teal-500/20 dark:bg-teal-500/10 blur-[100px] mix-blend-multiply dark:mix-blend-screen animate-pulse md:w-[600px] md:h-[600px]" style="animation-duration: 10s; animation-delay: 2s;"></div>
    </div>

    <!-- Sticky Navbar -->
    <nav class="sticky top-0 z-40 px-4 sm:px-6 lg:px-8 w-full backdrop-blur-xl bg-white/70 dark:bg-[#09090b]/70 border-b border-zinc-200/50 dark:border-white/5 transition-colors duration-300 relative">
        <div class="max-w-7xl mx-auto h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 text-2xl font-black tracking-tight text-zinc-900 dark:text-white group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 group-hover:scale-105 group-hover:shadow-emerald-500/40 transition-all duration-300">
                    <i class="fas fa-cube text-xl"></i>
                </div>
                Central<span class="text-emerald-500">3D</span>
            </div>
            <div class="flex items-center gap-3 sm:gap-6">
                <button id="themeToggleHome" class="w-10 h-10 rounded-full flex items-center justify-center text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800 transition-colors" title="Alternar tema">
                    <i class="fas fa-sun text-lg" id="themeIconHome"></i>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-tachometer-alt"></i> Meu Painel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-semibold text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors">Entrar</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-bold rounded-full transition-all duration-300 shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_30px_rgba(16,185,129,0.5)] hover:-translate-y-0.5">
                        Criar Loja Grátis
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative px-4 sm:px-6 lg:px-8 py-20 lg:py-32 flex flex-col items-center justify-center text-center max-w-5xl mx-auto z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider mb-8 animate-fade-in-up">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            O Marketplace da Comunidade
        </div>
        
        <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black mb-6 tracking-tighter leading-[1.1] animate-fade-in-up" style="animation-delay: 0.1s;">
            O Hub das <br class="hidden sm:block"> Melhores Lojas de <br class="hidden sm:block">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-400 drop-shadow-sm">Impressão 3D</span>
        </h1>
        
        <p class="text-lg sm:text-xl md:text-2xl text-zinc-600 dark:text-zinc-400 mb-10 max-w-3xl leading-relaxed font-medium animate-fade-in-up" style="animation-delay: 0.2s;">
            Encontre peças exclusivas, action figures e utilidades criadas pelas mentes mais brilhantes e seletas da nossa comunidade.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto animate-fade-in-up" style="animation-delay: 0.3s;">
            <a href="#lojas" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-base font-bold rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300">
                Explorar Lojas
            </a>
            <a href="{{ route('modeler-requests.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white text-base font-bold rounded-full transition-all duration-300 shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_30px_rgba(16,185,129,0.5)] hover:scale-105">
                <i class="fas fa-magic mr-2"></i> Procurar um Modelador
            </a>
            <a href="#ofertas" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-md border border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-white text-base font-bold rounded-full hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all duration-300">
                Ver Ofertas Globais
            </a>
        </div>
    </div>

    <!-- Platform Stats / Trust Strip -->
    <div class="border-y border-zinc-200/50 dark:border-white/5 bg-white/30 dark:bg-[#09090b]/50 backdrop-blur-md py-8 z-10 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-zinc-200/50 dark:divide-zinc-800/50">
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-black text-zinc-900 dark:text-white mb-1"><i class="fas fa-users text-emerald-500 mr-2"></i>100+</span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Makers Ativos</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-black text-zinc-900 dark:text-white mb-1"><i class="fas fa-boxes text-emerald-500 mr-2"></i>5k+</span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Produtos Cadastrados</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-black text-zinc-900 dark:text-white mb-1"><i class="fas fa-star text-emerald-500 mr-2"></i>4.9</span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Avaliação Média</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl font-black text-zinc-900 dark:text-white mb-1"><i class="fas fa-shield-alt text-emerald-500 mr-2"></i>100%</span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Compra Segura</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">

        <!-- Top Stores Section -->
        <div id="lojas" class="mb-32 scroll-mt-28">
            <div class="flex flex-col items-center text-center mb-12">
                <span class="text-orange-500 text-sm font-bold tracking-wider uppercase mb-2"><i class="fas fa-fire mr-1"></i> Ranking</span>
                <h2 class="text-3xl md:text-5xl font-black text-zinc-900 dark:text-white tracking-tight">Top Lojas da Comunidade</h2>
            </div>

            @if($topStores->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($topStores as $store)
                        <a href="{{ route('store.show', $store->slug) }}" class="group relative flex flex-col items-center p-8 bg-white/70 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-[2rem] hover:-translate-y-2 transition-all duration-500 overflow-hidden">
                            <!-- Hover gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/0 to-emerald-500/0 group-hover:from-emerald-500/5 group-hover:to-teal-500/10 transition-colors duration-500 z-0"></div>
                            
                            <div class="relative w-28 h-28 rounded-full shadow-[0_0_0_4px_rgba(255,255,255,1)] dark:shadow-[0_0_0_4px_rgba(39,39,42,1)] group-hover:shadow-[0_0_0_4px_rgba(16,185,129,0.2)] transition-shadow duration-500 mb-6 overflow-hidden flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 z-10">
                                @if($store->store_logo)
                                    <img src="{{ $store->store_logo_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $store->store_name }}">
                                @else
                                    <i class="fas fa-store text-3xl text-zinc-400 dark:text-zinc-600 group-hover:scale-110 transition-transform duration-700"></i>
                                @endif
                            </div>
                            
                            <h3 class="relative font-bold text-zinc-900 dark:text-white text-center text-xl mb-3 group-hover:text-emerald-500 transition-colors z-10">{{ $store->store_name }}</h3>
                            
                            <div class="relative flex items-center gap-2 text-sm font-bold text-orange-500 bg-orange-50 dark:bg-orange-500/10 px-4 py-1.5 rounded-full z-10">
                                <i class="fas fa-arrow-up"></i> {{ number_format($store->upvotes ?? 0) }} Upvotes
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-16 bg-white/50 dark:bg-zinc-900/30 backdrop-blur-md border border-dashed border-zinc-300 dark:border-white/10 rounded-3xl text-center">
                    <div class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-6">
                        <i class="fas fa-store-slash text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Nenhuma loja em destaque</h3>
                    <p class="text-zinc-500 dark:text-zinc-400">Crie sua loja e seja o primeiro do ranking mundial!</p>
                </div>
            @endif
        </div>

        <!-- Global Offers Section -->
        <div id="ofertas" class="mb-20 scroll-mt-28">
            <div class="flex flex-col items-center text-center mb-12">
                <span class="text-indigo-500 text-sm font-bold tracking-wider uppercase mb-2"><i class="fas fa-star mr-1"></i> Oportunidades</span>
                <h2 class="text-3xl md:text-5xl font-black text-zinc-900 dark:text-white tracking-tight">Ofertas Globais</h2>
            </div>

            @if($latestOffers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @foreach($latestOffers as $offer)
                        <div class="group flex flex-col bg-white/70 dark:bg-zinc-900/40 backdrop-blur-xl border border-zinc-200 dark:border-white/10 rounded-[2rem] hover:-translate-y-2 transition-all duration-500 overflow-hidden shadow-sm hover:shadow-xl dark:shadow-none dark:hover:shadow-[0_0_30px_rgba(0,0,0,0.5)]">
                            <div class="relative aspect-[4/3] bg-zinc-100/50 dark:bg-zinc-950/50 flex items-center justify-center overflow-hidden p-6 z-0">
                                @if($offer->image_path)
                                    <img src="{{ $offer->thumbnail_url }}" class="w-full h-full object-contain filter drop-shadow-lg group-hover:scale-110 transition-transform duration-700" alt="{{ $offer->name }}">
                                @else
                                    <i class="fas fa-box text-6xl text-zinc-300 dark:text-zinc-700 group-hover:scale-110 transition-transform duration-700"></i>
                                @endif
                                
                                @if($offer->discount_percent)
                                    <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1.5 rounded-xl font-black text-sm shadow-lg backdrop-blur-md">
                                        -{{ $offer->discount_percent }}%
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-6 flex flex-col flex-1 relative bg-white/50 dark:bg-zinc-900/50 z-10">
                                @php
                                    $owner = \App\Models\User::find($offer->user_id) ?? \App\Models\User::where('is_admin', true)->first();
                                @endphp
                                
                                <div class="flex items-center gap-2 mb-3">
                                    @if($owner && $owner->store_logo)
                                        <img src="{{ $owner->store_logo_url }}" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-500 text-[10px]">
                                            <i class="fas fa-store"></i>
                                        </div>
                                    @endif
                                    <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 capitalize tracking-wide">{{ $owner->store_name ?? 'Central3D' }}</span>
                                </div>
                                
                                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2 leading-tight group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors line-clamp-2">{{ $offer->name }}</h3>
                                
                                <div class="mt-auto pt-6 flex flex-wrap items-end gap-2">
                                    <span class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight">R$ {{ number_format($offer->price, 2, ',', '.') }}</span>
                                    @if($offer->original_price && $offer->original_price > $offer->price)
                                        <span class="text-sm font-semibold text-zinc-400 line-through mb-1">R$ {{ number_format($offer->original_price, 2, ',', '.') }}</span>
                                    @endif
                                </div>
                                
                                <a href="{{ $offer->affiliate_url }}" target="_blank" class="mt-6 block w-full text-center px-4 py-3.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-xl hover:scale-[1.02] transition-transform shadow-md">
                                    Pegar Oferta <i class="fas fa-external-link-alt ml-2 text-xs opacity-70"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-16 bg-white/50 dark:bg-zinc-900/30 backdrop-blur-md border border-dashed border-zinc-300 dark:border-white/10 rounded-3xl text-center">
                    <div class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-6">
                        <i class="fas fa-tag text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Nenhuma oferta no momento</h3>
                    <p class="text-zinc-500 dark:text-zinc-400">Volte mais tarde para encontrar descontos incríveis!</p>
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <footer class="mt-20 pt-10 border-t border-zinc-200/50 dark:border-white/5 text-center text-sm font-medium text-zinc-500 dark:text-zinc-400">
            &copy; {{ date('Y') }} Central3D. Todos os direitos reservados.
        </footer>
    </div>
</div>

<style>
    @keyframes fade-in-up {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
</style>

@endsection

@section('scripts')
<script>
    // Specific Theme Toggle for Home Page
    const homeThemeToggle = document.getElementById('themeToggleHome');
    const homeThemeIcon = document.getElementById('themeIconHome');
    
    function updateHomeThemeIcon() {
        if(!homeThemeIcon) return;
        const isDark = document.documentElement.classList.contains('dark');
        homeThemeIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
        
        // Ensure nav styling switches cleanly by toggling colors on elements that need explicit handling
        // Though most handle via Tailwind `dark:` classes automatically.
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
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            const target = document.querySelector(targetId);
            if(target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
@endsection
