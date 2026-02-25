@extends('layouts.app')

@section('page-title', 'Produtos')

@section('top-actions')
    <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
        <i class="fas fa-plus"></i> Novo Produto
    </a>
@endsection

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
    <form method="GET" class="w-full sm:w-auto flex-1 max-w-md relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-zinc-400"></i>
        </div>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar produtos..." 
               class="w-full pl-10 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
    </form>

    @if($categories->count() > 0)
        <form method="GET" class="w-full sm:w-auto">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="category" onchange="this.form.submit()" 
                    class="w-full sm:w-48 px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                <option value="">Todas categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col group">
                <div class="aspect-square bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center relative overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <i class="fas fa-cube text-4xl text-zinc-300 dark:text-zinc-600 group-hover:scale-110 transition-transform duration-300"></i>
                    @endif
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm bg-white/90 dark:bg-zinc-900/90 text-zinc-700 dark:text-zinc-300 backdrop-blur-sm border border-zinc-200 dark:border-zinc-700">
                            {{ $product->category ?? 'Geral' }}
                        </span>
                    </div>
                </div>
                
                <div class="p-4 sm:p-5 flex flex-col flex-1">
                    <h3 class="font-semibold text-zinc-900 dark:text-white text-lg leading-tight mb-1 truncate">{{ $product->name }}</h3>
                    
                    <div class="flex items-center justify-between mt-3 mb-4">
                        <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">R$ {{ number_format($product->base_price, 2, ',', '.') }}</span>
                        <span class="flex items-center gap-1.5 text-sm {{ $product->current_stock <= 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-500 dark:text-zinc-400' }}">
                            <i class="fas fa-box-open text-xs"></i> 
                            {{ $product->current_stock }} un.
                        </span>
                    </div>

                    @if($product->variations->count() > 0)
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            @foreach($product->variations->take(3) as $variation)
                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 truncate max-w-full">
                                    {{ $variation->name }}
                                </span>
                            @endforeach
                            @if($product->variations->count() > 3)
                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                    +{{ $product->variations->count() - 3 }}
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="h-6 mb-4"></div> {{-- Spacer --}}
                    @endif

                    <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                        <a href="{{ route('products.edit', $product) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-zinc-50 text-zinc-700 hover:bg-zinc-100 dark:bg-zinc-800/50 dark:text-zinc-300 dark:hover:bg-zinc-800 text-sm font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 transition-colors">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" 
                              onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center justify-center w-9 h-9 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-medium rounded-lg border border-red-200 dark:border-red-500/20 transition-colors" title="Excluir">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination placeholder --}}
    <div class="mt-6"></div>
@else
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-1">Nenhum produto cadastrado</h3>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 max-w-sm mx-auto">Adicione seu primeiro produto para começar a vender e gerenciar seu estoque.</p>
        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
            <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
        </a>
    </div>
@endif
@endsection
