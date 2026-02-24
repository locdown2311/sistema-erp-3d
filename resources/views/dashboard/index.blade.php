@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-boxes-stacked"></i></div>
        <div class="stat-info">
            <div class="stat-label">Produtos Ativos</div>
            <div class="stat-value">{{ $totalProducts }}</div>
        </div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon cyan"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total de Vendas</div>
            <div class="stat-value">{{ $totalSales }}</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <div class="stat-label">Receita Total</div>
            <div class="stat-value">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-list-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Tarefas Pendentes</div>
            <div class="stat-value">{{ $pendingTasks }}</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    {{-- Sales Chart --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line" style="margin-right: 8px; color: var(--primary-light);"></i>Vendas — Últimos 30 dias</h3>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div>
        {{-- Low Stock --}}
        <div class="card" style="margin-bottom: var(--space-lg);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: var(--warning);"></i>Estoque Baixo</h3>
            </div>
            @if($lowStock->count() > 0)
                @foreach($lowStock as $product)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border);">
                        <span>{{ $product->name }}</span>
                        <span class="badge {{ $product->current_stock <= 0 ? 'badge-danger' : 'badge-warning' }}">
                            {{ $product->current_stock }} un.
                        </span>
                    </div>
                @endforeach
            @else
                <p class="text-muted" style="font-size: 0.85rem;">Nenhum produto com estoque baixo</p>
            @endif
        </div>

        {{-- Upcoming Tasks --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-check" style="margin-right: 8px; color: var(--accent);"></i>Próximas Tarefas</h3>
            </div>
            @if($upcomingTasks->count() > 0)
                @foreach($upcomingTasks as $task)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border);">
                        <div>
                            <span style="font-weight: 500;">{{ $task->title }}</span>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $task->due_date->format('d/m/Y') }}</div>
                        </div>
                        <span class="badge badge-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'success') }}">
                            {{ $task->priority === 'high' ? 'Alta' : ($task->priority === 'medium' ? 'Média' : 'Baixa') }}
                        </span>
                    </div>
                @endforeach
            @else
                <p class="text-muted" style="font-size: 0.85rem;">Nenhuma tarefa pendente</p>
            @endif
        </div>
    </div>
</div>

{{-- Recent Sales --}}
<div class="card mt-2">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-receipt" style="margin-right: 8px; color: var(--success);"></i>Vendas Recentes</h3>
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nova Venda</a>
    </div>
    @if($recentSales->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Itens</th>
                        <th>Total</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->customer_name ?? '—' }}</td>
                            <td>{{ $sale->items->count() }} produto(s)</td>
                            <td class="text-success">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td>{{ $sale->sale_date ? $sale->sale_date->format('d/m/Y') : $sale->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <p>Nenhuma venda registrada ainda</p>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">Registrar Primeira Venda</a>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        const salesData = @json($salesChart);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(s => {
                    const d = new Date(s.date);
                    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Vendas (R$)',
                    data: salesData.map(s => parseFloat(s.total)),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#16a34a',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(148, 163, 184, 0.06)' },
                        ticks: { color: '#64748b', font: { size: 11 } },
                    },
                    y: {
                        grid: { color: 'rgba(148, 163, 184, 0.06)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: v => 'R$ ' + v.toFixed(0),
                        },
                    }
                }
            }
        });
    }
</script>
@endsection
