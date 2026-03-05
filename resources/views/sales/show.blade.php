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

    {{-- Shipping / Tracking Card --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-truck text-indigo-500"></i> Informações de Entrega
            </h3>
            @if($sale->shipping_status)
                @php
                    $statusColors = [
                        'Pendente' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                        'Em Trânsito' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border-blue-200 dark:border-blue-500/20',
                        'Entregue' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                        'Devolvido' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border-red-200 dark:border-red-500/20',
                    ];
                    $statusIcons = [
                        'Pendente' => 'fas fa-clock',
                        'Em Trânsito' => 'fas fa-shipping-fast',
                        'Entregue' => 'fas fa-check-circle',
                        'Devolvido' => 'fas fa-undo-alt',
                    ];
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full border {{ $statusColors[$sale->shipping_status] ?? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700' }}">
                    <i class="{{ $statusIcons[$sale->shipping_status] ?? 'fas fa-question-circle' }} text-[10px]"></i>
                    {{ $sale->shipping_status }}
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                    <i class="fas fa-minus-circle text-[10px]"></i> Não enviado
                </span>
            @endif
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg text-sm text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('sales.tracking.update', $sale) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">Código de Rastreio</label>
                        <input type="text" name="tracking_code" value="{{ old('tracking_code', $sale->tracking_code) }}" placeholder="Ex: BR123456789BR"
                            class="w-full px-4 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">Status do Frete</label>
                        <select name="shipping_status"
                            class="w-full px-4 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                            <option value="" {{ !$sale->shipping_status ? 'selected' : '' }}>— Não enviado —</option>
                            <option value="Pendente" {{ $sale->shipping_status === 'Pendente' ? 'selected' : '' }}>📦 Pendente</option>
                            <option value="Em Trânsito" {{ $sale->shipping_status === 'Em Trânsito' ? 'selected' : '' }}>🚚 Em Trânsito</option>
                            <option value="Entregue" {{ $sale->shipping_status === 'Entregue' ? 'selected' : '' }}>✅ Entregue</option>
                            <option value="Devolvido" {{ $sale->shipping_status === 'Devolvido' ? 'selected' : '' }}>↩️ Devolvido</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-save"></i> Atualizar Rastreio
                </button>
            </form>
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
