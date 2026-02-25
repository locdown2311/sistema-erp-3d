@extends('layouts.app')

@section('page-title', 'Estoque')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 xl:gap-8">
    {{-- Stock Overview --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
            <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-warehouse text-amber-500"></i> Estoque Atual
            </h3>
        </div>

        @if($products->count() > 0)
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-zinc-50 dark:bg-zinc-900 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-4 font-medium">Produto</th>
                            <th class="px-6 py-4 font-medium text-center">Estoque</th>
                            <th class="px-6 py-4 font-medium text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                        @foreach($products as $product)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $product->name }}</div>
                                    @if($product->variations->count() > 0)
                                        <div class="text-xs text-zinc-500 mt-0.5">
                                            {{ $product->variations->count() }} variação(ões)
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-zinc-900 dark:text-white">{{ $product->current_stock }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if($product->current_stock <= 0)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">Sem Estoque</span>
                                    @elseif($product->current_stock <= 5)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">Baixo</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Normal</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center flex-1 flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-1">Nenhum produto cadastrado</h3>
                <p class="text-sm text-zinc-500 mb-4">Adicione produtos para visualizar o estoque</p>
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                    <i class="fas fa-plus"></i> Cadastrar Produto
                </a>
            </div>
        @endif
    </div>

    <div class="flex flex-col gap-6 xl:gap-8">
        {{-- Add Movement --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-exchange-alt text-blue-500"></i> Nova Movimentação
                </h3>
            </div>

            <form method="POST" action="{{ route('stock.store') }}" class="p-5 sm:p-6">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Produto <span class="text-red-500">*</span></label>
                    <select name="product_id" required 
                            class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                        <option value="">Selecione...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tipo <span class="text-red-500">*</span></label>
                        <select name="type" required 
                                class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                            <option value="in">Entrada (+)</option>
                            <option value="out">Saída (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Quantidade <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" min="1" value="1" required 
                               class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Observação</label>
                    <input type="text" name="notes" placeholder="Ex: Produção do lote #12" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                    <i class="fas fa-check"></i> Registrar Movimentação
                </button>
            </form>
        </div>

        {{-- Recent Movements --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex-1">
            <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-history text-zinc-400"></i> Movimentações Recentes
                </h3>
            </div>
            
            @if($movements->count() > 0)
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($movements as $mov)
                        <div class="flex items-center gap-4 px-5 sm:px-6 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <span class="inline-flex items-center justify-center w-10 py-1.5 text-xs font-bold rounded-md shrink-0 {{ $mov->type === 'in' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20' }}">
                                {{ $mov->type === 'in' ? '+' : '-' }}{{ $mov->quantity }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm text-zinc-900 dark:text-white truncate">{{ $mov->product->name }}</div>
                                @if($mov->notes)
                                    <div class="text-xs text-zinc-500 truncate mt-0.5">{{ $mov->notes }}</div>
                                @endif
                            </div>
                            <span class="text-xs text-zinc-400 whitespace-nowrap shrink-0">{{ $mov->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    <p class="text-sm text-zinc-500">Nenhuma movimentação registrada</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
