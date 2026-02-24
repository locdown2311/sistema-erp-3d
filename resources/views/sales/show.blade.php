@extends('layouts.app')

@section('page-title', 'Venda #' . $sale->id)

@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-receipt" style="margin-right: 8px; color: var(--success);"></i>Detalhes da Venda</h3>
        <span class="badge {{ $sale->status === 'completed' ? 'badge-success' : ($sale->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
            {{ $sale->status === 'completed' ? 'Concluída' : ($sale->status === 'pending' ? 'Pendente' : 'Cancelada') }}
        </span>
    </div>

    <div class="form-row" style="margin-bottom: var(--space-lg);">
        <div>
            <span class="form-label">Cliente</span>
            <p style="font-weight: 500;">{{ $sale->customer_name ?? 'Não informado' }}</p>
        </div>
        <div>
            <span class="form-label">Data</span>
            <p style="font-weight: 500;">{{ $sale->sale_date->format('d/m/Y') }}</p>
        </div>
    </div>

    @if($sale->notes)
        <div style="margin-bottom: var(--space-lg);">
            <span class="form-label">Observações</span>
            <p>{{ $sale->notes }}</p>
        </div>
    @endif

    <div class="table-container" style="margin-bottom: var(--space-lg);">
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Variação</th>
                    <th>Qtd</th>
                    <th>Preço Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td style="font-weight: 500;">{{ $item->product->name ?? 'Removido' }}</td>
                        <td>{{ $item->variation->name ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td class="text-success" style="font-weight: 600;">R$ {{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="text-align: right; padding: var(--space-md); background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(6, 182, 212, 0.05)); border-radius: var(--radius-md);">
        <span style="font-size: 0.85rem; color: var(--text-secondary);">Total</span>
        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-light);">R$ {{ number_format($sale->total, 2, ',', '.') }}</div>
    </div>

    <div style="margin-top: var(--space-lg);">
        <a href="{{ route('sales.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>
@endsection
