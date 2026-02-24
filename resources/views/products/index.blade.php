@extends('layouts.app')

@section('page-title', 'Produtos')

@section('top-actions')
    <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Produto</a>
@endsection

@section('content')
<div class="filters-bar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" class="form-control" placeholder="Buscar produtos..."
                   value="{{ request('search') }}" style="padding-left: 38px;">
        </form>
    </div>
    @if($categories->count() > 0)
        <form method="GET" style="display:contents;">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <select name="category" class="form-control" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
                <option value="">Todas categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if($products->count() > 0)
    <div class="products-grid">
        @foreach($products as $product)
            <div class="product-card">
                <div class="product-image">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
                    @else
                        <i class="fas fa-cube placeholder-icon"></i>
                    @endif
                </div>
                <div class="product-info">
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-category">{{ $product->category ?? 'Sem categoria' }}</div>
                    <div class="product-meta">
                        <span class="product-price">R$ {{ number_format($product->base_price, 2, ',', '.') }}</span>
                        <span class="product-stock">
                            <i class="fas fa-box"></i> {{ $product->current_stock }} un.
                        </span>
                    </div>
                    @if($product->variations->count() > 0)
                        <div style="margin-top: 8px;">
                            @foreach($product->variations->take(3) as $variation)
                                <span class="badge badge-primary" style="margin: 2px 0;">{{ $variation->name }}</span>
                            @endforeach
                            @if($product->variations->count() > 3)
                                <span class="badge badge-info">+{{ $product->variations->count() - 3 }}</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="product-actions">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Editar</a>
                    <form method="POST" action="{{ route('products.destroy', $product) }}" style="display:inline;"
                          onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination">
        {{-- Products not paginated --}}
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-boxes-stacked"></i>
        <p>Nenhum produto cadastrado</p>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Cadastrar Primeiro Produto</a>
    </div>
@endif
@endsection
