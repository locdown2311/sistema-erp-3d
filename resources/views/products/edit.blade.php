@extends('layouts.app')

@section('page-title', 'Editar Produto')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-6xl">
    {{-- Edit Form --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden h-fit mb-6 lg:mb-0">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-edit text-indigo-500"></i> Editar Produto
            </h3>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Produto <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Categoria</label>
                <input type="text" name="category" value="{{ old('category', $product->category) }}" list="categories" 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                <datalist id="categories">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>

            <div class="mb-5">
            <x-image-upload :current-image="$product->image_path" :image-url="$product->thumbnail_url" />
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Imagens Adicionais</label>
            
            @if($product->images->count() > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-3">
                    @foreach($product->images as $img)
                        <div class="relative group">
                            <div class="aspect-square rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ $img->thumbnail_url }}" alt="Extra" class="w-full h-full object-cover" loading="lazy" referrerpolicy="no-referrer">
                            </div>
                            <label class="absolute top-1.5 right-1.5 cursor-pointer" title="Marcar para remover">
                                <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="sr-only peer">
                                <div class="w-6 h-6 rounded-md bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm border border-zinc-300 dark:border-zinc-600 flex items-center justify-center text-transparent peer-checked:bg-red-500 peer-checked:border-red-500 peer-checked:text-white transition-all">
                                    <i class="fas fa-times text-xs"></i>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-zinc-500 mb-3">Marque o <strong>X</strong> nas imagens que deseja remover.</p>
            @endif

            <input type="file" name="extra_images[]" multiple accept="image/*" class="w-full text-sm text-zinc-500 dark:text-zinc-400
                file:mr-4 file:py-2 file:px-4
                file:rounded-l-lg file:border-0
                file:text-sm file:font-medium
                file:bg-indigo-50 file:text-indigo-700
                dark:file:bg-indigo-500/10 dark:file:text-indigo-400
                hover:file:bg-indigo-100 dark:hover:file:bg-indigo-500/20
                border border-zinc-300 dark:border-zinc-800 rounded-lg bg-zinc-50 dark:bg-zinc-950 cursor-pointer transition-colors">
            <p class="mt-1 text-xs text-zinc-500">Adicionar mais fotos (até 5 no total).</p>
        </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none resize-none">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço de Venda <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                        </div>
                        <input type="number" name="base_price" step="0.01" min="0" value="{{ old('base_price', $product->base_price) }}" required 
                               class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Custo Base <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                        </div>
                        <input type="number" name="base_cost" step="0.01" min="0" value="{{ old('base_cost', $product->base_cost) }}" required 
                               class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tempo de Impressão</label>
                    <div class="relative">
                        <input type="number" name="print_time_hours" step="0.01" min="0" value="{{ old('print_time_hours', $product->print_time_hours) }}" 
                               class="w-full pr-14 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">horas</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Peso do Filamento</label>
                    <div class="relative">
                        <input type="number" name="weight_grams" step="0.01" min="0" value="{{ old('weight_grams', $product->weight_grams) }}" 
                               class="w-full pr-8 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">g</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="active" id="active" class="sr-only peer" {{ old('active', $product->active) ? 'checked' : '' }}>
                    <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-emerald-600"></div>
                    <span class="ms-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Produto ativo na loja web</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900">
                    <i class="fas fa-check"></i> Salvar Alterações
                </button>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    {{-- Variations --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden h-fit">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-layer-group text-emerald-500"></i> Variações
            </h3>
        </div>

        <div class="p-6">
            @if($product->variations->count() > 0)
                <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-800 rounded-lg mb-8">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nome</th>
                                <th class="px-4 py-3 font-medium">Tipo</th>
                                <th class="px-4 py-3 font-medium">Mod. Preço</th>
                                <th class="px-4 py-3 font-medium text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                            @foreach($product->variations as $variation)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $variation->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-400">
                                            {{ $variation->type === 'color' ? 'Cor' : ($variation->type === 'size' ? 'Tamanho' : 'Material') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-emerald-600 dark:text-emerald-400 font-medium">
                                        {{ $variation->price_modifier >= 0 ? '+' : '' }}R$ {{ number_format($variation->price_modifier, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('products.variations.destroy', [$product, $variation]) }}"
                                              onsubmit="return confirm('Tem certeza que deseja remover esta variação?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6 mb-8 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-900/50">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Nenhuma variação cadastrada</p>
                </div>
            @endif

            <form method="POST" action="{{ route('products.variations.store', $product) }}" class="bg-zinc-50 dark:bg-zinc-900/50 p-5 rounded-lg border border-zinc-200 dark:border-zinc-800">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome da Variação</label>
                    <input type="text" name="name" required placeholder="Ex: Azul, Grande, PLA+" 
                           class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tipo</label>
                        <select name="type" 
                                class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                            <option value="color">Cor</option>
                            <option value="size">Tamanho</option>
                            <option value="material">Material</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Mod. Preço (+/-)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-xs text-xs mt-0.5">R$</span>
                            </div>
                            <input type="number" name="price_modifier" step="0.01" value="0.00" 
                                   class="w-full pl-8 pr-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Mod. Custo (+/-)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-xs text-xs mt-0.5">R$</span>
                            </div>
                            <input type="number" name="cost_modifier" step="0.01" value="0.00" 
                                   class="w-full pl-8 pr-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">SKU</label>
                        <input type="text" name="sku" placeholder="Opcional" 
                               class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
                    </div>
                </div>
                
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-sm">
                    <i class="fas fa-plus"></i> Adicionar Variação
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
