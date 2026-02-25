@extends('layouts.app')

@section('page-title', 'Novo Produto')

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden max-w-3xl mb-6">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-plus-circle text-indigo-500"></i> Cadastrar Produto
        </h3>
    </div>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-6">
        @csrf

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Produto <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Vaso Geométrico" 
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Categoria</label>
            <input type="text" name="category" value="{{ old('category') }}" placeholder="Ex: Decoração" list="categories" 
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            <datalist id="categories">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">
                @endforeach
            </datalist>
        </div>

        <div class="mb-5">
            <x-image-upload />
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição</label>
            <textarea name="description" rows="3" placeholder="Descreva o produto..." 
                      class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none resize-none placeholder-zinc-400 dark:placeholder-zinc-600">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço de Venda <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                    </div>
                    <input type="number" name="base_price" step="0.01" min="0" value="{{ old('base_price', '0.00') }}" required 
                           class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Custo Base <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                    </div>
                    <input type="number" name="base_cost" step="0.01" min="0" value="{{ old('base_cost', '0.00') }}" required 
                           class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tempo de Impressão</label>
                <div class="relative">
                    <input type="number" name="print_time_hours" step="0.01" min="0" value="{{ old('print_time_hours') }}" placeholder="Ex: 4.5" 
                           class="w-full pr-14 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">horas</span>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Peso do Filamento</label>
                <div class="relative">
                    <input type="number" name="weight_grams" step="0.01" min="0" value="{{ old('weight_grams') }}" placeholder="Ex: 120" 
                           class="w-full pr-8 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">g</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900">
                <i class="fas fa-check"></i> Salvar Produto
            </button>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
