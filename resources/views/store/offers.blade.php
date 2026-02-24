<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofertas — {{ $store->store_name }}</title>
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
        .store-logo { width:80px; height:80px; border-radius:var(--radius-lg); overflow:hidden; flex-shrink:0; border:2px solid var(--glass-border); background:var(--bg-card); display:flex; align-items:center; justify-content:center; }
        .store-logo img { width:100%; height:100%; object-fit:cover; }
        .store-logo i { font-size:2rem; color:var(--text-muted); }
        .store-info h1 { font-size:1.5rem; font-weight:700; color:var(--text-primary); }
        .store-nav { display:flex; gap:var(--space-sm); margin-left:var(--space-xl); }
        .store-nav a {
            padding: 6px 16px; border-radius: var(--radius-md); font-size: 0.82rem; font-weight: 500;
            color: var(--text-secondary); text-decoration: none; transition: all 0.2s;
        }
        .store-nav a:hover { background: rgba(255,255,255,0.05); color: var(--text-primary); }
        .store-nav a.active { background: var(--primary); color: white; }
        .store-actions { margin-left:auto; }
        .offers-container { padding: var(--space-xl) var(--space-2xl); max-width: 1200px; margin: 0 auto; }
        .offers-header {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }
        .offers-header h2 { font-size:1.1rem; font-weight:600; color:var(--text-secondary); }
        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: var(--space-lg);
        }
        .offer-card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all 0.2s;
            position: relative;
        }
        .offer-card:hover { transform: translateY(-4px); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .offer-img {
            height: 200px;
            background: linear-gradient(135deg, rgba(255,107,53,0.08), rgba(255,68,68,0.05));
            display: flex; align-items: center; justify-content: center; overflow: hidden; padding: var(--space-sm);
        }
        .offer-img img { width:100%; height:100%; object-fit:contain; }
        .offer-img i { font-size:2.5rem; color:var(--text-muted); }
        .offer-badge {
            position: absolute; top: 12px; right: 12px;
            background: linear-gradient(135deg, #ff6b35, #ff4444);
            color: white; padding: 3px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 700;
        }
        .offer-info { padding: var(--space-md); }
        .offer-name { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
        .offer-cat { font-size: 0.75rem; color: var(--text-muted); margin-bottom: var(--space-sm); }
        .offer-desc { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--space-md); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .offer-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding: var(--space-sm) var(--space-md) var(--space-md);
        }
        .offer-prices { display: flex; flex-direction: column; }
        .offer-price { font-size: 1.1rem; font-weight: 700; color: var(--success-light); }
        .offer-original { font-size: 0.78rem; color: var(--text-muted); text-decoration: line-through; }
        .btn-shopee {
            background: linear-gradient(135deg, #ff6b35, #ee4d2d);
            color: white; padding: 8px 16px; border-radius: var(--radius-md);
            font-size: 0.82rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.2s; border: none; cursor: pointer;
        }
        .btn-shopee:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(238,77,45,0.3); color: white; }
        .empty-offers { text-align:center; padding:var(--space-2xl); color:var(--text-muted); }
        .empty-offers i { font-size:3rem; opacity:0.4; margin-bottom:var(--space-md); }
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
            <a href="{{ route('store.show', $store->slug) }}">Produtos</a>
            <a href="{{ route('store.offers', $store->slug) }}" class="active">🔥 Ofertas</a>
        </div>
        <div class="store-actions">
            @auth
                @if(auth()->id() === $store->id)
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm"><i class="fas fa-tachometer-alt"></i> Painel</a>
                @endif
            @endauth
        </div>
    </div>

    <div class="offers-container">
        <div class="offers-header">
            <h2><i class="fas fa-fire" style="color:#ff6b35; margin-right:8px;"></i> Ofertas Imperdíveis</h2>
            <span style="font-size:0.8rem; color:var(--text-muted);">({{ $offers->count() }} ofertas)</span>
        </div>

        @if($offers->count() > 0)
            <div class="offers-grid">
                @foreach($offers as $offer)
                    <div class="offer-card">
                        @if($offer->discount_percent)
                            <div class="offer-badge">-{{ $offer->discount_percent }}%</div>
                        @endif
                        <div class="offer-img">
                            @if($offer->image_path)
                                <img src="{{ asset('storage/' . $offer->image_path) }}" alt="{{ $offer->name }}">
                            @else
                                <i class="fas fa-tag"></i>
                            @endif
                        </div>
                        <div class="offer-info">
                            <div class="offer-name">{{ $offer->name }}</div>
                            <div class="offer-cat">{{ $offer->category }}</div>
                            @if($offer->description)
                                <div class="offer-desc">{{ $offer->description }}</div>
                            @endif
                        </div>
                        <div class="offer-footer">
                            <div class="offer-prices">
                                <span class="offer-price">R$ {{ number_format($offer->price, 2, ',', '.') }}</span>
                                @if($offer->original_price)
                                    <span class="offer-original">R$ {{ number_format($offer->original_price, 2, ',', '.') }}</span>
                                @endif
                            </div>
                            <a href="{{ $offer->affiliate_url }}" target="_blank" rel="noopener" class="btn-shopee">
                                <i class="fas fa-shopping-cart"></i> Ver na Shopee
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-offers">
                <i class="fas fa-tags"></i>
                <p>Nenhuma oferta disponível no momento</p>
            </div>
        @endif
    </div>
</body>
</html>
