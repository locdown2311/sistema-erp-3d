<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $store->store_name }} — Loja</title>
    <meta property="og:title" content="{{ $store->store_name }} — Loja">
    <meta property="og:site_name" content="{{ $store->store_name }}">
    @if($store->store_logo)
        <meta property="og:image" content="{{ asset('storage/' . $store->store_logo) }}">
        <meta property="twitter:image" content="{{ asset('storage/' . $store->store_logo) }}">
        <link rel="icon" href="{{ asset('storage/' . $store->store_logo) }}">
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
        
        .bg-store-primary { background-color: var(--store-primary); }
        .text-store-primary { color: var(--store-primary); }
        .border-store-primary { border-color: var(--store-primary); }
        
        /* Store specific gradients and glows using CSS variables for Tailwind integration */
        .store-header-bg {
            background: linear-gradient(135deg, color-mix(in srgb, var(--store-primary) 15%, transparent), color-mix(in srgb, var(--store-accent) 5%, transparent));
        }
        .store-card-hover:hover {
            border-color: color-mix(in srgb, var(--store-primary) 30%, transparent);
            box-shadow: 0 15px 40px -10px color-mix(in srgb, var(--store-primary) 15%, rgba(0,0,0,0.3));
        }
        .store-img-bg {
            background: linear-gradient(135deg, color-mix(in srgb, var(--store-primary) 10%, transparent), color-mix(in srgb, var(--store-accent) 5%, transparent));
        }
        .store-nav-active {
            background-color: var(--store-primary) !important;
            color: white !important;
        }
    </style>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col font-sans antialiased selection:bg-zinc-200 dark:selection:bg-zinc-800">
    <header class="store-header-bg border-b border-zinc-200/50 dark:border-zinc-800/50 pt-8 sm:pt-12 pb-6 px-6 sm:px-8 shadow-sm">
        <div class="max-w-6xl mx-auto w-full">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 sm:gap-8">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden flex-shrink-0 border-4 border-white dark:border-zinc-900 bg-white dark:bg-zinc-800 shadow-xl flex items-center justify-center transform -rotate-3 transition-transform hover:rotate-0 duration-300">
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
                        @if($isOwner)
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold rounded-xl hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-all shadow-md">
                                <i class="fas fa-tachometer-alt"></i> Painel
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-semibold rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all shadow-sm">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    @endauth
                </div>
            </div>

            <nav class="flex items-center justify-center sm:justify-start gap-2 mt-8 sm:mt-10 mb-[-24px] overflow-x-auto no-scrollbar pb-2">
                <a href="{{ route('store.show', $store->slug) }}" class="store-nav-active px-5 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap transition-all shadow-md">
                    <i class="fas fa-box-open mr-2"></i> Produtos
                </a>
                <a href="{{ route('store.offers', $store->slug) }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-white whitespace-nowrap transition-all">
                    🔥 Ofertas
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1 w-full max-w-6xl mx-auto px-6 sm:px-8 py-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg store-img-bg flex items-center justify-center text-store-primary">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                Catálogo de Produtos <span class="text-sm font-medium px-2.5 py-0.5 rounded-full bg-zinc-200 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 mb-0.5">{{ $products->count() }}</span>
            </h2>
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
                @foreach($products as $product)
                    <div class="group bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm transition-all duration-300 store-card-hover flex flex-col h-full transform" id="product-card-{{ $product->id }}">
                        <div class="store-img-bg relative aspect-[4/3] w-full flex items-center justify-center overflow-hidden p-6">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-contain filter drop-shadow-lg group-hover:scale-110 transition-transform duration-500">
                            @else
                                <i class="fas fa-cube text-5xl text-store-primary opacity-50 group-hover:scale-110 transition-transform duration-500"></i>
                            @endif
                        </div>
                        
                        <div class="p-5 sm:p-6 flex flex-col flex-1">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight mb-1 group-hover:text-store-primary transition-colors">{{ $product->name }}</h3>
                            
                            @if($product->category)
                                <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-4">{{ $product->category }}</div>
                            @else
                                <div class="mb-4"></div>
                            @endif

                            @if($product->variations->count() > 0)
                                <div class="mt-auto mb-5 w-full">
                                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5 ml-1">Opção</label>
                                    <select class="w-full px-3 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg text-sm font-medium text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-store-primary focus:border-transparent transition-all outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat cursor-pointer" 
                                            id="var-{{ $product->id }}" 
                                            onchange="updateProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $store->whatsapp ? preg_replace('/\D/', '', $store->whatsapp) : '' }}')">
                                        <option value="" data-modifier="0">Selecione...</option>
                                        @foreach($product->variations as $var)
                                            <option value="{{ $var->name }}" data-modifier="{{ $var->price_modifier }}">
                                                {{ $var->name }} (+R$ {{ number_format($var->price_modifier, 2, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="mt-auto flex-1"></div>
                            @endif
                            
                            <div class="flex items-center justify-between pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500 dark:from-emerald-400 dark:to-teal-300" id="price-{{ $product->id }}" data-base-price="{{ $product->base_price }}">
                                    R$ {{ number_format($product->base_price, 2, ',', '.') }}
                                </span>

                                @if(!$isOwner && $store->whatsapp)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $store->whatsapp) }}?text={{ urlencode('Olá! Tenho interesse no produto: ' . $product->name . ' (R$ ' . number_format($product->base_price, 2, ',', '.') . '). Está disponível?') }}"
                                       target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] text-white shadow-lg shadow-[#25D366]/20 hover:shadow-[#25D366]/40 hover:-translate-y-1 transition-all duration-300" id="btn-wa-{{ $product->id }}" title="Comprar via WhatsApp">
                                        <i class="fab fa-whatsapp text-lg"></i>
                                    </a>
                                @elseif($isOwner)
                                    <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors" title="Editar Produto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <script>
                function updateProduct(productId, productName, phone) {
                    const select = document.getElementById('var-' + productId);
                    const option = select.options[select.selectedIndex];
                    const modifier = parseFloat(option.getAttribute('data-modifier') || 0);
                    
                    const priceEl = document.getElementById('price-' + productId);
                    const basePrice = parseFloat(priceEl.getAttribute('data-base-price'));
                    const finalPrice = basePrice + modifier;
                    
                    priceEl.innerText = 'R$ ' + finalPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    const btnWa = document.getElementById('btn-wa-' + productId);
                    if (btnWa && phone) {
                        let variationText = option.value ? ` - Variação: ${option.value}` : '';
                        let text = `Olá! Tenho interesse no produto: ${productName}${variationText} (R$ ${finalPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}). Está disponível?`;
                        btnWa.href = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
                    }
                }
            </script>
        @else
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl p-16 text-center max-w-2xl mx-auto mt-12 shadow-sm">
                <div class="w-24 h-24 rounded-full store-img-bg flex items-center justify-center mx-auto mb-6 text-store-primary text-opacity-50 text-4xl">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">Nenhum produto cadastrado</h3>
                <p class="text-zinc-500 dark:text-zinc-400 text-base">Esta loja ainda não possui produtos disponíveis no catálogo. Volte novamente mais tarde!</p>
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
