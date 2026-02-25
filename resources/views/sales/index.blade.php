@extends('layouts.app')

@section('page-title', 'Vendas')

@section('top-actions')
    <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
        <i class="fas fa-plus"></i> Nova Venda
    </a>
@endsection

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end sm:items-center">
        <div class="w-full sm:w-auto flex-1">
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Status</label>
            <select name="status" onchange="this.form.submit()" 
                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                <option value="">Todos os status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluída</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Data Inicial</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Data Final</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
        </div>

        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
            <i class="fas fa-filter text-zinc-400"></i> Filtrar
        </button>
    </form>
</div>

@if($sales->count() > 0)
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-6 py-4 font-medium">#</th>
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium text-center">Itens</th>
                        <th class="px-6 py-4 font-medium text-right">Total</th>
                        <th class="px-6 py-4 font-medium text-center">Status</th>
                        <th class="px-6 py-4 font-medium">Data</th>
                        <th class="px-6 py-4 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($sales as $sale)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">{{ $sale->id }}</td>
                            <td class="px-6 py-4">{{ $sale->customer_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ $sale->items->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($sale->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Concluída</span>
                                @elseif($sale->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">Pendente</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">Cancelada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-white dark:hover:bg-zinc-800 transition-colors" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta venda?');">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($sales->hasPages())
        <div class="mt-6">
            {{ $sales->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    @endif
@else
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
            <i class="fas fa-cash-register"></i>
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-1">Nenhuma venda encontrada</h3>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 max-w-sm mx-auto">Você ainda não registrou nenhuma venda ou os filtros não retornaram resultados.</p>
        <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
            <i class="fas fa-plus"></i> Registrar Primeira Venda
        </a>
    </div>
@endif
@endsection
