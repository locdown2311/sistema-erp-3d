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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root {
            --primary: {{ $store->store_color_primary ?? '#16a34a' }};
            --primary-light: {{ $store->store_color_primary ?? '#16a34a' }}dd;
            --primary-glow: {{ $store->store_color_primary ?? '#16a34a' }}33;
            --accent: {{ $store->store_color_accent ?? '#86efac' }};
            --accent-light: {{ $store->store_color_accent ?? '#86efac' }}dd;
        }
        body { padding: 0; }
        .store-header {
            background: linear-gradient(135deg, var(--primary-glow), {{ $store->store_color_accent ?? '#86efac' }}15);
            border-bottom: 1px solid var(--glass-border);
            padding: var(--space-xl) var(--space-2xl);
            display: flex;
            align-items: center;
            gap: var(--space-xl);
        }
        .store-logo {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--glass-border);
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .store-logo img { width: 100%; height: 100%; object-fit: cover; }
        .store-logo i { font-size: 2rem; color: var(--text-muted); }
        .store-info h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
        .store-info p { font-size: 0.9rem; color: var(--text-muted); margin-top: var(--space-xs); }
        .store-actions { margin-left: auto; display: flex; gap: var(--space-sm); }
        .store-container { padding: var(--space-xl) var(--space-2xl); max-width: 1200px; margin: 0 auto; }
        .store-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: var(--space-lg);
            margin-top: var(--space-lg);
        }
        .store-product-card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all 0.2s;
        }
        .store-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(22, 163, 74, 0.2);
        }
        .store-product-img {
            height: 200px;
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.1), rgba(134, 239, 172, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: var(--space-sm);
        }
        .store-product-img img { width: 100%; height: 100%; object-fit: contain; }
        .store-product-img i { font-size: 2.5rem; color: var(--text-muted); }
        .store-product-info { padding: var(--space-md); }
        .store-product-name { font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: var(--space-xs); }
        .store-product-cat { font-size: 0.78rem; color: var(--text-muted); margin-bottom: var(--space-md); }
        .store-product-bot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-sm) var(--space-md) var(--space-md);
        }
        .store-product-price { font-size: 1.1rem; font-weight: 700; color: var(--success-light); }
        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-whatsapp:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            color: white;
        }
        .empty-store {
            text-align: center;
            padding: var(--space-2xl);
            color: var(--text-muted);
        }
        .empty-store i { font-size: 3rem; opacity: 0.4; margin-bottom: var(--space-md); }
        .store-nav { display:flex; gap:var(--space-sm); margin-left:var(--space-xl); }
        .store-nav a {
            padding:6px 16px; border-radius:var(--radius-md); font-size:0.82rem; font-weight:500;
            color:var(--text-secondary); text-decoration:none; transition:all 0.2s;
        }
        .store-nav a:hover { background:rgba(255,255,255,0.05); color:var(--text-primary); }
        .store-nav a.active { background:var(--primary); color:white; }
    </style>
</head>
<body>
    <div class="store-header">
        <div class="store-logo">
            @if($store->store_logo)
                <img src="{{ asset('storage/' . $store->store_logo) }}" alt="{{ $store->store_name }}">
            @else
                <i class="fas fa-store"></i>
            @endif
        </div>
        <div class="store-info">
            <h1>{{ $store->store_name }}</h1>
        </div>
        <div class="store-nav">
            <a href="{{ route('store.show', $store->slug) }}" class="active">Produtos</a>
            <a href="{{ route('store.offers', $store->slug) }}">🔥 Ofertas</a>
        </div>
        <div class="store-actions">
            @auth
                @if($isOwner)
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm"><i class="fas fa-tachometer-alt"></i> Painel</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Login</a>
            @endauth
        </div>
    </div>

    <div class="store-container">
        <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-secondary);">
            <i class="fas fa-boxes-stacked" style="margin-right: 8px; color: var(--primary-light);"></i>
            Produtos ({{ $products->count() }})
        </h2>

        @if($products->count() > 0)
            <div class="store-products-grid">
                @foreach($products as $product)
                    <div class="store-product-card" id="product-card-{{ $product->id }}">
                        <div class="store-product-img">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <i class="fas fa-cube"></i>
                            @endif
                        </div>
                        <div class="store-product-info">
                            <div class="store-product-name">{{ $product->name }}</div>
                            @if($product->category)
                                <div class="store-product-cat">{{ $product->category }}</div>
                            @endif

                            @if($product->variations->count() > 0)
                                <div style="margin-top: var(--space-md); width: 100%;">
                                    <select class="form-control" style="font-size: 0.85rem; padding: 0.4rem; height: auto; background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border);" 
                                            id="var-{{ $product->id }}" 
                                            onchange="updateProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $store->whatsapp ? preg_replace('/\D/', '', $store->whatsapp) : '' }}')">
                                        <option value="" data-modifier="0">Selecione uma opção...</option>
                                        @foreach($product->variations as $var)
                                            <option value="{{ $var->name }}" data-modifier="{{ $var->price_modifier }}">
                                                {{ $var->name }} (+R$ {{ number_format($var->price_modifier, 2, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <div class="store-product-bot" style="margin-top: auto; padding-top: var(--space-md);">
                            <span class="store-product-price" id="price-{{ $product->id }}" data-base-price="{{ $product->base_price }}">
                                R$ {{ number_format($product->base_price, 2, ',', '.') }}
                            </span>

                            @if(!$isOwner && $store->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $store->whatsapp) }}?text={{ urlencode('Olá! Tenho interesse no produto: ' . $product->name . ' (R$ ' . number_format($product->base_price, 2, ',', '.') . '). Está disponível?') }}"
                                   target="_blank" class="btn-whatsapp" id="btn-wa-{{ $product->id }}">
                                    <i class="fab fa-whatsapp"></i> Comprar
                                </a>
                            @elseif($isOwner)
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            @endif
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
                    
                    // Update visual price (BR format)
                    priceEl.innerText = 'R$ ' + finalPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    // Update WhatsApp link if exists
                    const btnWa = document.getElementById('btn-wa-' + productId);
                    if (btnWa && phone) {
                        let variationText = option.value ? ` - Variação: ${option.value}` : '';
                        let text = `Olá! Tenho interesse no produto: ${productName}${variationText} (R$ ${finalPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}). Está disponível?`;
                        btnWa.href = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
                    }
                }
            </script>
        @else
            <div class="empty-store">
                <i class="fas fa-box-open"></i>
                <p>Esta loja ainda não tem produtos</p>
            </div>
        @endif
    </div>
</body>
</html>
