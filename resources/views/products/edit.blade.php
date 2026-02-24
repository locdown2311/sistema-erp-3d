@extends('layouts.app')

@section('page-title', 'Editar Produto')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl); max-width: 1100px;">
    {{-- Edit Form --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-edit" style="margin-right: 8px; color: var(--primary-light);"></i>Editar Produto</h3>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nome do Produto *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}" list="categories">
                    <datalist id="categories">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <x-image-upload :currentImage="$product->image_path" />

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Preço de Venda (R$) *</label>
                    <input type="number" name="base_price" class="form-control" step="0.01" min="0" value="{{ old('base_price', $product->base_price) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Custo Base (R$) *</label>
                    <input type="number" name="base_cost" class="form-control" step="0.01" min="0" value="{{ old('base_cost', $product->base_cost) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tempo de Impressão (horas)</label>
                    <input type="number" name="print_time_hours" class="form-control" step="0.01" min="0" value="{{ old('print_time_hours', $product->print_time_hours) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Peso do Filamento (g)</label>
                    <input type="number" name="weight_grams" class="form-control" step="0.01" min="0" value="{{ old('weight_grams', $product->weight_grams) }}">
                </div>
            </div>

            <div class="form-check" style="margin-bottom: var(--space-lg);">
                <input type="checkbox" name="active" id="active" {{ old('active', $product->active) ? 'checked' : '' }}>
                <label for="active" class="form-label" style="margin: 0;">Produto ativo</label>
            </div>

            <div style="display: flex; gap: var(--space-sm);">
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Salvar</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

    {{-- Variations --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-layer-group" style="margin-right: 8px; color: var(--accent);"></i>Variações</h3>
        </div>

        @if($product->variations->count() > 0)
            <div class="table-container" style="margin-bottom: var(--space-lg);">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Mod. Preço</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->variations as $variation)
                            <tr>
                                <td>{{ $variation->name }}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $variation->type === 'color' ? 'Cor' : ($variation->type === 'size' ? 'Tamanho' : 'Material') }}
                                    </span>
                                </td>
                                <td>{{ $variation->price_modifier >= 0 ? '+' : '' }}R$ {{ number_format($variation->price_modifier, 2, ',', '.') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('products.variations.destroy', [$product, $variation]) }}"
                                          onsubmit="return confirm('Remover esta variação?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted" style="font-size: 0.85rem; margin-bottom: var(--space-lg);">Nenhuma variação cadastrada</p>
        @endif

        <form method="POST" action="{{ route('products.variations.store', $product) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nome da Variação</label>
                <input type="text" name="name" class="form-control" required placeholder="Ex: Azul, Grande, PLA+">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="type" class="form-control">
                        <option value="color">Cor</option>
                        <option value="size">Tamanho</option>
                        <option value="material">Material</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mod. Preço (R$)</label>
                    <input type="number" name="price_modifier" class="form-control" step="0.01" value="0.00">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Mod. Custo (R$)</label>
                    <input type="number" name="cost_modifier" class="form-control" step="0.01" value="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" placeholder="Opcional">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar Variação</button>
        </form>
    </div>
</div>
@endsection
