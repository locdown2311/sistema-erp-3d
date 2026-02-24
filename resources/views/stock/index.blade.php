@extends('layouts.app')

@section('page-title', 'Estoque')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl);">
    {{-- Stock Overview --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-warehouse" style="margin-right: 8px; color: var(--accent);"></i>Estoque Atual</h3>
        </div>

        @if($products->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Estoque</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">{{ $product->name }}</div>
                                    @if($product->variations->count() > 0)
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            {{ $product->variations->count() }} variação(ões)
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $product->current_stock }}</td>
                                <td>
                                    @if($product->current_stock <= 0)
                                        <span class="badge badge-danger">Sem Estoque</span>
                                    @elseif($product->current_stock <= 5)
                                        <span class="badge badge-warning">Baixo</span>
                                    @else
                                        <span class="badge badge-success">Normal</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Cadastre produtos primeiro</p>
            </div>
        @endif
    </div>

    <div>
        {{-- Add Movement --}}
        <div class="card" style="margin-bottom: var(--space-lg);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exchange-alt" style="margin-right: 8px; color: var(--primary-light);"></i>Nova Movimentação</h3>
            </div>

            <form method="POST" action="{{ route('stock.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Produto *</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo *</label>
                        <select name="type" class="form-control" required>
                            <option value="in">📦 Entrada</option>
                            <option value="out">📤 Saída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantidade *</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observação</label>
                    <input type="text" name="notes" class="form-control" placeholder="Ex: Produção do lote #12">
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Registrar</button>
            </form>
        </div>

        {{-- Recent Movements --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history" style="margin-right: 8px; color: var(--text-muted);"></i>Movimentações Recentes</h3>
            </div>
            @if($movements->count() > 0)
                @foreach($movements as $mov)
                    <div style="display: flex; align-items: center; gap: var(--space-md); padding: 8px 0; border-bottom: 1px solid var(--border);">
                        <span class="badge {{ $mov->type === 'in' ? 'badge-success' : 'badge-danger' }}">
                            {{ $mov->type === 'in' ? '+ ' . $mov->quantity : '- ' . $mov->quantity }}
                        </span>
                        <div style="flex: 1;">
                            <div style="font-weight: 500; font-size: 0.85rem;">{{ $mov->product->name }}</div>
                            @if($mov->notes)
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $mov->notes }}</div>
                            @endif
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $mov->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            @else
                <p class="text-muted" style="font-size: 0.85rem;">Nenhuma movimentação registrada</p>
            @endif
        </div>
    </div>
</div>
@endsection
