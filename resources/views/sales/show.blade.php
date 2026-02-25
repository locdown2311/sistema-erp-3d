@extends('layouts.app')

@section('page-title', 'Venda #' . $sale->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-receipt text-emerald-500"></i> Detalhes da Venda
            </h3>
            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : ($sale->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20') }}">
                {{ $sale->status === 'completed' ? 'Concluída' : ($sale->status === 'pending' ? 'Pendente' : 'Cancelada') }}
            </span>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Cliente</span>
                    <p class="font-medium text-zinc-900 dark:text-white text-base">{{ $sale->customer_name ?? 'Não informado' }}</p>
                </div>
                <div>
                    <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Data da Venda</span>
                    <p class="font-medium text-zinc-900 dark:text-white text-base">{{ $sale->sale_date->format('d/m/Y') }}</p>
                </div>
            </div>

            @if($sale->notes)
                <div class="mb-8 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Observações</span>
                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $sale->notes }}</p>
                </div>
            @endif

            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-box-open text-zinc-400"></i> Itens da Venda
            </h4>
            
            <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-3 font-medium">Produto</th>
                                <th class="px-4 py-3 font-medium text-center">Variação</th>
                                <th class="px-4 py-3 font-medium text-center">Qtd</th>
                                <th class="px-4 py-3 font-medium text-right">Preço Unit.</th>
                                <th class="px-4 py-3 font-medium text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                            @foreach($sale->items as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $item->product->name ?? 'Removido' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->variation)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $item->variation->name }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-white">R$ {{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col items-end gap-1 p-5 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total da Venda</span>
                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($sale->total, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar para Vendas
        </a>
        
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>

<style type="text/css" media="print">
    @page { margin: 1cm; }
    body { background: white !important; color: black !important; padding: 0 !important; }
    .sidebar, .top-header, footer, a.btn, button.btn, button:not([type="submit"]), a[href^="{{ route('sales.index') }}"] { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; max-width: 100% !important; margin: 0 !important; }
    .dark\:bg-zinc-900 { background-color: white !important; }
    .dark\:border-zinc-800 { border-color: #e5e7eb !important; border-width: 1px !important;}
    .dark\:text-white { color: black !important; }
    .dark\:text-zinc-400, .dark\:text-zinc-300 { color: #4b5563 !important; }
    .dark\:bg-zinc-800 { background-color: #f3f4f6 !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #ccc !important; padding: 8px !important; }
    .bg-emerald-100, .text-emerald-700 { background: transparent !important; color: black !important; border: 1px solid black !important; }
    .text-emerald-600, .dark\:text-emerald-400 { color: black !important; font-weight: bold !important; }
</style>
@endsection
