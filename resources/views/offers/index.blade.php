@extends('layouts.app')

@section('page-title', 'Ofertas')

@section('top-actions')
    <a href="{{ route('offers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-sm">
        <i class="fas fa-plus"></i> Nova Oferta
    </a>
@endsection

@section('content')
@if($offers->count() > 0)
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-6 py-4 font-medium">Imagem</th>
                        <th class="px-6 py-4 font-medium">Nome</th>
                        <th class="px-6 py-4 font-medium">Preço</th>
                        <th class="px-6 py-4 font-medium">Desconto</th>
                        <th class="px-6 py-4 font-medium">Categoria</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($offers as $offer)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-4 w-16">
                            @if($offer->image_path)
                                <img src="{{ $offer->thumbnail_url }}" alt="" class="w-12 h-12 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center border border-zinc-200 dark:border-zinc-700">
                                    <i class="fas fa-image text-zinc-400"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-normal min-w-[200px]">
                            <strong class="font-semibold text-zinc-900 dark:text-white block mb-0.5">{{ $offer->name }}</strong>
                            @if($offer->description)
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 block line-clamp-2 max-w-xs">{{ $offer->description }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 block">R$ {{ number_format($offer->price, 2, ',', '.') }}</span>
                            @if($offer->original_price)
                                <span class="text-xs text-zinc-400 dark:text-zinc-500 line-through block mt-0.5">R$ {{ number_format($offer->original_price, 2, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($offer->discount_percent)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-full bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-sm">
                                    -{{ $offer->discount_percent }}%
                                </span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center text-xs text-zinc-600 dark:text-zinc-400">{{ $offer->category }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($offer->active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Ativa</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">Inativa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $offer->affiliate_url }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:text-blue-400 dark:hover:bg-blue-500/10 transition-colors" title="Ver Link Externo">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="{{ route('offers.edit', $offer) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-white dark:hover:bg-zinc-800 transition-colors" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('offers.destroy', $offer) }}" onsubmit="return confirm('Tem certeza que deseja remover esta oferta?')">
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
@else
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center mx-auto mb-4 text-orange-500 text-2xl">
            <i class="fas fa-tags"></i>
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-2">Nenhuma oferta cadastrada</h3>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 max-w-sm mx-auto">Adicione ofertas com links de afiliado para exibir em todas as lojas conectadas e gerar receita extra.</p>
        <a href="{{ route('offers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
            <i class="fas fa-plus"></i> Criar Primeira Oferta
        </a>
    </div>
@endif
@endsection
