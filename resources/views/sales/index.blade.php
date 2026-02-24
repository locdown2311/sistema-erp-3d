@extends('layouts.app')

@section('page-title', 'Vendas')

@section('top-actions')
    <a href="{{ route('sales.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Venda</a>
@endsection

@section('content')
<div class="filters-bar">
    <form method="GET" style="display: flex; gap: var(--space-md); flex-wrap: wrap; width: 100%;">
        <select name="status" class="form-control" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
            <option value="">Todos os status</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluída</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
        </select>
        <input type="date" name="date_from" class="form-control" style="width: auto;" value="{{ request('date_from') }}" placeholder="De">
        <input type="date" name="date_to" class="form-control" style="width: auto;" value="{{ request('date_to') }}" placeholder="Até">
        <button type="submit" class="btn btn-outline"><i class="fas fa-filter"></i> Filtrar</button>
    </form>
</div>

@if($sales->count() > 0)
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Itens</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td style="font-weight: 600;">{{ $sale->id }}</td>
                            <td>{{ $sale->customer_name ?? '—' }}</td>
                            <td>{{ $sale->items->count() }} produto(s)</td>
                            <td style="font-weight: 600;" class="text-success">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td>
                                @if($sale->status === 'completed')
                                    <span class="badge badge-success">Concluída</span>
                                @elseif($sale->status === 'pending')
                                    <span class="badge badge-warning">Pendente</span>
                                @else
                                    <span class="badge badge-danger">Cancelada</span>
                                @endif
                            </td>
                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline btn-sm btn-icon" title="Ver"><i class="fas fa-eye"></i></a>
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Excluir esta venda?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        {{ $sales->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-cash-register"></i>
        <p>Nenhuma venda encontrada</p>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">Registrar Primeira Venda</a>
    </div>
@endif
@endsection
