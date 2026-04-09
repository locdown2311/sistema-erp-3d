<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Links de {{ $store->store_name }}</title>
    <meta property="og:title" content="Links de {{ $store->store_name }}">
    <meta property="og:site_name" content="{{ $store->store_name }}">
    @if($store->store_logo)
        <meta property="og:image" content="{{ $store->store_logo_url }}">
        <meta property="twitter:image" content="{{ $store->store_logo_url }}">
        <link rel="icon" href="{{ $store->store_logo_thumbnail_url }}">
    @endif
    <meta property="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --store-primary: {{ $store->store_color_primary ?? '#10b981' }};
            --store-accent: {{ $store->store_color_accent ?? '#34d399' }};
        }
        
        body {
            background-color: var(--store-primary);
            background-image: radial-gradient(circle at top right, color-mix(in srgb, var(--store-accent) 40%, transparent), transparent 70%),
                              radial-gradient(circle at bottom left, color-mix(in srgb, var(--store-primary) 80%, black 20%), transparent 80%);
            background-attachment: fixed;
            color: #ffffff;
        }

        .dark body {
            background-color: #09090b; /* zinc-950 */
            background-image: radial-gradient(circle at top right, color-mix(in srgb, var(--store-accent) 15%, transparent), transparent 50%),
                              radial-gradient(circle at bottom left, color-mix(in srgb, var(--store-primary) 15%, transparent), transparent 50%);
        }

        .link-card {
            background: rgba(255, 255, 255, 0.95);
            color: var(--store-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .link-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            background: #ffffff;
            color: var(--store-primary);
        }

        .dark .link-card {
            background: rgba(24, 24, 27, 0.8); /* zinc-900 */
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f4f4f5; /* zinc-100 */
        }

        .dark .link-card:hover {
            background: rgba(39, 39, 42, 0.9); /* zinc-800 */
            border-color: var(--store-primary);
        }

        .brand-icon-wrapper {
            background: linear-gradient(135deg, var(--store-primary), var(--store-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .dark .brand-icon-wrapper {
            background: none;
            -webkit-background-clip: unset;
            -webkit-text-fill-color: unset;
            color: var(--store-primary);
        }

        /* Entrance Animation */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up {
            animation: fadeUp 0.6s ease forwards;
            opacity: 0;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans antialiased selection:bg-white/30 selection:text-white">

    <div class="fixed top-4 right-4 z-50">
        <button id="themeToggleLinks" class="w-10 h-10 rounded-full bg-black/20 hover:bg-black/40 backdrop-blur-md text-white flex items-center justify-center transition-all shadow-lg" title="Alternar tema">
            <i class="fas fa-sun" id="themeIconLinks"></i>
        </button>
    </div>

    <main class="flex-1 w-full max-w-xl mx-auto px-6 py-12 flex flex-col items-center justify-center min-h-screen">
        
        <!-- Profile Section -->
        <div class="text-center w-full mb-10 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden mx-auto mb-5 border-4 border-white/50 dark:border-zinc-800 shadow-2xl bg-white dark:bg-zinc-900">
                @if($store->store_logo)
                    <img src="{{ $store->store_logo_url }}" alt="{{ $store->store_name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-white dark:bg-zinc-800">
                        <i class="fas fa-store text-4xl" style="color: var(--store-primary);"></i>
                    </div>
                @endif
            </div>
            
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2 {{ rtrim(substr($store->store_color_primary, 1), '0') == '' ? 'text-zinc-900 dark:text-white' : 'text-white' }} drop-shadow-md">
                {{ $store->store_name }}
            </h1>
            
            <p class="text-white/90 dark:text-zinc-400 text-sm font-medium mb-1">
                {{ '@' . $store->slug }}
            </p>
            
            @if($store->description)
                <p class="text-white/80 dark:text-zinc-300 text-sm sm:text-base max-w-md mx-auto mt-4 px-4 leading-relaxed font-light">
                    {{ $store->description }}
                </p>
            @endif
        </div>

        <!-- Links Section -->
        <div class="w-full space-y-4 mb-12">
            @if($links->count() > 0)
                @foreach($links as $index => $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" 
                       class="link-card flex items-center p-4 sm:p-5 rounded-2xl shadow-lg w-full group animate-fade-up"
                       style="animation-delay: {{ 0.2 + ($index * 0.05) }}s;">
                        
                        <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-zinc-800/50 mr-4 transition-colors group-hover:bg-gray-100 dark:group-hover:bg-zinc-800">
                            @php
                                $iconMap = [
                                    'link' => 'fas fa-link',
                                    'instagram' => 'fab fa-instagram',
                                    'tiktok' => 'fab fa-tiktok',
                                    'whatsapp' => 'fab fa-whatsapp',
                                    'youtube' => 'fab fa-youtube',
                                    'facebook' => 'fab fa-facebook',
                                    'x-twitter' => 'fab fa-x-twitter',
                                    'globe' => 'fas fa-globe',
                                    'envelope' => 'fas fa-envelope',
                                    'map-marker-alt' => 'fas fa-map-marker-alt',
                                ];
                                $iconClass = isset($iconMap[$link->icon]) ? $iconMap[$link->icon] : 'fas fa-link';
                            @endphp
                            <i class="{{ $iconClass }} text-xl brand-icon-wrapper"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0 pr-4">
                            <h2 class="text-base sm:text-lg font-bold truncate">
                                {{ $link->title }}
                            </h2>
                        </div>
                        
                        <div class="flex-shrink-0 text-gray-400 group-hover:text-[var(--store-primary)] transition-colors dark:text-zinc-500">
                            <i class="fas fa-ellipsis-h"></i>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="text-center py-8 text-white/70 dark:text-zinc-500 animate-fade-up" style="animation-delay: 0.2s;">
                    <p>Nenhum link adicionado ainda.</p>
                </div>
            @endif
            
            <!-- Store specific link -->
            <a href="{{ route('store.show', $store->slug) }}" 
               class="link-card flex items-center justify-center p-4 sm:p-5 rounded-2xl shadow-lg w-full group animate-fade-up border-2 border-transparent"
               style="animation-delay: {{ 0.2 + ($links->count() * 0.05) }}s;">
                <div class="flex items-center gap-3 font-bold text-center">
                    <i class="fas fa-box-open text-xl brand-icon-wrapper"></i>
                    Visite Nossa Loja Online
                </div>
            </a>
            
        </div>

        <!-- Footer -->
        <div class="mt-auto pt-8 text-center animate-fade-up pb-6" style="animation-delay: 0.8s;">
             <a href="{{ url('/') }}" class="inline-flex flex-col items-center gap-2 group">
                 <span class="text-xs text-white/60 dark:text-zinc-500 uppercase tracking-widest font-semibold">Desenvolvido por</span>
                 <div class="flex items-center gap-2 text-white/90 dark:text-zinc-400 group-hover:text-white dark:group-hover:text-white transition-colors">
                     <i class="fas fa-cube text-lg"></i>
                     <span class="font-bold tracking-tight">Central 3D</span>
                 </div>
             </a>
        </div>
    </main>

    <script>
        // Init theme
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        const themeToggleLinks = document.getElementById('themeToggleLinks');
        const themeIconLinks = document.getElementById('themeIconLinks');
        
        function updateThemeIconLinks() {
            if(!themeIconLinks) return;
            const isDark = document.documentElement.classList.contains('dark');
            themeIconLinks.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
        }
        
        updateThemeIconLinks();

        if (themeToggleLinks) {
            themeToggleLinks.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateThemeIconLinks();
            });
        }
    </script>
</body>
</html>
