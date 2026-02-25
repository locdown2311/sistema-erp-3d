@extends('layouts.app')

@section('page-title', 'O Marketplace Definitivo da Impressão 3D')

@section('content')
<style>
    /* Public Home Layout Fixes to align with app.blade.php structure */
    body { padding-top: 20px; }
    .sidebar { display: none !important; }
    .main-content { margin: 0 auto !important; max-width: 100% !important; width: 100%; padding: 0 2rem !important; }
    
    .hero-section {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white;
        border-radius: var(--radius-lg);
        padding: 4rem 2rem;
        text-align: center;
        margin-bottom: var(--space-xl);
        position: relative;
        overflow: hidden;
    }
    .hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    .hero-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        max-width: 600px;
        margin-inline: auto;
    }
    .hero-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-hero {
        background: white;
        color: var(--primary);
        font-weight: 700;
        padding: 0.8rem 1.5rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-hero-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: var(--space-lg);
        gap: var(--space-sm);
    }
    .section-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-color);
    }

    .store-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: var(--space-md);
        margin-bottom: var(--space-xl);
    }
    
    .store-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: var(--space-md);
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .store-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px var(--shadow-color);
        border-color: var(--primary-light);
    }
    .store-logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: var(--space-sm);
        border: 2px solid var(--border);
        background: var(--bg-body);
    }
    .store-name {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: var(--space-xs);
    }
    .store-upvotes {
        color: var(--accent);
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }

    .offer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--space-md);
        margin-bottom: var(--space-xl);
    }
    .offer-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
    }
    .offer-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px var(--shadow-color);
    }
    .offer-img-container {
        height: 180px;
        background: var(--bg-body);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }
    .offer-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .offer-content {
        padding: var(--space-md);
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .offer-store {
        font-size: 0.8rem;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: var(--space-xs);
    }
    .offer-name {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: var(--space-xs);
        color: var(--text-color);
    }
    .offer-price {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--accent);
        margin-top: auto;
        padding-top: var(--space-sm);
    }
    .offer-original-price {
        font-size: 0.9rem;
        color: var(--text-muted);
        text-decoration: line-through;
        margin-left: var(--space-xs);
        font-weight: normal;
    }
    .offer-btn {
        display: block;
        text-align: center;
        background: var(--primary);
        color: white;
        text-decoration: none;
        padding: 0.6rem;
        font-weight: 600;
        border-radius: var(--radius-sm);
        margin-top: var(--space-md);
        transition: background 0.2s;
    }
    .offer-btn:hover { background: var(--primary-dark); }
</style>

<!-- Public Navbar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg); padding: var(--space-sm) var(--space-md); background: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border);">
    <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-cube"></i> Central3D
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;"><i class="fas fa-tachometer-alt"></i> Meu Painel</a>
        @else
            <a href="{{ route('login') }}" style="color: var(--text-color); font-weight: 600; text-decoration: none;">Entrar</a>
            <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Criar Loja Grátis</a>
        @endauth
    </div>
</div>

<div class="hero-section">
    <h1 class="hero-title">O Hub das Melhores Lojas de Impressão 3D</h1>
    <p class="hero-subtitle">Encontre peças exclusivas, action figures e utilidades impressas pelas melhores lojas selecionadas pela comunidade.</p>
    <div class="hero-buttons">
        <a href="#lojas" class="btn-hero">Explorar Lojas</a>
        <a href="#ofertas" class="btn-hero btn-hero-outline">Ver Ofertas</a>
    </div>
</div>

<div id="lojas">
    <div class="section-header">
        <i class="fas fa-fire" style="font-size: 1.5rem; color: #ef4444;"></i>
        <h2>Top Lojas da Comunidade</h2>
    </div>

    @if($topStores->count() > 0)
        <div class="store-grid">
            @foreach($topStores as $store)
                <a href="{{ route('store.show', $store->slug) }}" class="store-card">
                    @if($store->store_logo)
                        <img src="{{ asset('storage/' . $store->store_logo) }}" class="store-logo" alt="{{ $store->store_name }}">
                    @else
                        <div class="store-logo" style="display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fas fa-store" style="font-size: 2rem; color: var(--text-muted);"></i>
                        </div>
                    @endif
                    <div class="store-name">{{ $store->store_name }}</div>
                    <div class="store-upvotes">
                        <i class="fas fa-arrow-up"></i> {{ number_format($store->upvotes ?? 0) }} upvotes
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-store-alt-slash" style="font-size: 3rem; color: var(--border); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--text-muted);">Nenhuma loja em destaque no momento.</h3>
            <p>Crie sua loja e seja o primeiro do ranking mundial!</p>
        </div>
    @endif
</div>

<div id="ofertas" style="margin-top: 4rem;">
    <div class="section-header">
        <i class="fas fa-tags" style="font-size: 1.5rem; color: var(--accent);"></i>
        <h2>Ofertas Globais em Destaque</h2>
    </div>

    @if($latestOffers->count() > 0)
        <div class="offer-grid">
            @foreach($latestOffers as $offer)
                <div class="offer-card">
                    <div class="offer-img-container">
                        @if($offer->image_path)
                            <img src="{{ asset('storage/' . $offer->image_path) }}" class="offer-img" alt="{{ $offer->name }}">
                        @else
                            <i class="fas fa-box" style="font-size: 3rem; color: var(--border);"></i>
                        @endif
                        
                        @if($offer->discount_percent)
                            <div style="position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 0.2rem 0.5rem; border-radius: var(--radius-sm); font-weight: 800; font-size: 0.8rem;">
                                -{{ $offer->discount_percent }}%
                            </div>
                        @endif
                    </div>
                    <div class="offer-content">
                        @php
                            $owner = \App\Models\User::find($offer->user_id) ?? \App\Models\User::where('is_admin', true)->first();
                        @endphp
                        
                        @if($owner)
                            <div class="offer-store"><i class="fas fa-store"></i> {{ $owner->store_name ?? 'Central3D' }}</div>
                        @endif
                        
                        <div class="offer-name">{{ $offer->name }}</div>
                        
                        <div class="offer-price">
                            R$ {{ number_format($offer->price, 2, ',', '.') }}
                            @if($offer->original_price && $offer->original_price > $offer->price)
                                <span class="offer-original-price">R$ {{ number_format($offer->original_price, 2, ',', '.') }}</span>
                            @endif
                        </div>
                        
                        <a href="{{ $offer->affiliate_url }}" target="_blank" class="offer-btn">
                            Ver Oferta <i class="fas fa-external-link-alt" style="margin-left: 5px; font-size: 0.8rem;"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-tag" style="font-size: 3rem; color: var(--border); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--text-muted);">Nenhuma oferta disponível no momento.</h3>
            <p>Volte mais tarde para encontrar descontos incríveis!</p>
        </div>
    @endif
</div>

@endsection
