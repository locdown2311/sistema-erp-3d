@extends('layouts.app')

@section('page-title', 'Novo Produto')

@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle" style="margin-right: 8px; color: var(--primary-light);"></i>Cadastrar Produto</h3>
    </div>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label class="form-label">Nome do Produto *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Ex: Vaso Geométrico">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Categoria</label>
                <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="Ex: Decoração" list="categories">
                <datalist id="categories">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>
        </div>

        <x-image-upload />

        <div class="form-group">
            <label class="form-label">Descrição</label>
            <textarea name="description" class="form-control" placeholder="Descreva o produto...">{{ old('description') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Preço de Venda (R$) *</label>
                <input type="number" name="base_price" class="form-control" step="0.01" min="0" value="{{ old('base_price', '0.00') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Custo Base (R$) *</label>
                <input type="number" name="base_cost" class="form-control" step="0.01" min="0" value="{{ old('base_cost', '0.00') }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tempo de Impressão (horas)</label>
                <input type="number" name="print_time_hours" class="form-control" step="0.01" min="0" value="{{ old('print_time_hours') }}" placeholder="Ex: 4.5">
            </div>
            <div class="form-group">
                <label class="form-label">Peso do Filamento (g)</label>
                <input type="number" name="weight_grams" class="form-control" step="0.01" min="0" value="{{ old('weight_grams') }}" placeholder="Ex: 120">
            </div>
        </div>

        <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-lg);">
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Salvar Produto</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
