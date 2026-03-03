@extends('layouts.app')

@section('page-title', 'Minha Lista de Desejos')

@section('top-actions')
    <div class="flex items-center gap-2">
        @if($wishlists->count() > 0)
        <form action="{{ route('wishlists.refresh-all') }}" method="POST" class="inline-block" onsubmit="this.querySelector('button').innerHTML='<i class=\'fas fa-sync fa-spin mr-2\'></i>Atualizando...'; this.querySelector('button').classList.add('opacity-75', 'cursor-not-allowed')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                <i class="fas fa-sync-alt"></i> Atualizar Preços
            </button>
        </form>
        @endif
        
        <button onclick="openWishlistModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-sm">
            <i class="fas fa-plus"></i> Salvar Produto
        </button>
    </div>
@endsection

@section('content')
@if($wishlists->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
        @foreach($wishlists as $wishlist)
            <div class="group flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                <div class="relative h-48 sm:h-52 bg-zinc-100 dark:bg-zinc-800/50 flex items-center justify-center overflow-hidden">
                    @if($wishlist->image_url)
                        <img src="{{ $wishlist->image_url }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <i class="fas fa-image text-4xl text-zinc-300 dark:text-zinc-600"></i>
                    @endif
                    
                    <div class="absolute top-3 right-3 flex gap-2">
                        <form action="{{ route('wishlists.destroy', $wishlist) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este item da lista?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-full bg-white/90 dark:bg-zinc-900/90 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/20 flex items-center justify-center shadow-sm backdrop-blur-sm transition-colors" title="Remover">
                                <i class="fas fa-heart-broken text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="font-bold text-zinc-900 dark:text-white mb-2 line-clamp-2 text-sm leading-tight" title="{{ $wishlist->title }}">{{ $wishlist->title ?? 'Produto sem título' }}</h3>
                    
                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <div>
                            @if($wishlist->price)
                                <div class="flex items-center gap-2">
                                    <div class="font-black text-emerald-600 dark:text-emerald-400">R$ {{ number_format($wishlist->price, 2, ',', '.') }}</div>
                                    
                                    @if($wishlist->previous_price && $wishlist->previous_price != $wishlist->price)
                                        @if($wishlist->price < $wishlist->previous_price)
                                            <div class="flex items-center text-[10px] font-bold text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded" title="Preço anterior: R$ {{ number_format($wishlist->previous_price, 2, ',', '.') }}">
                                                <i class="fas fa-arrow-down mr-1"></i>
                                                {{ number_format((($wishlist->previous_price - $wishlist->price) / $wishlist->previous_price) * 100, 0) }}%
                                            </div>
                                        @else
                                            <div class="flex items-center text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-500/10 px-1.5 py-0.5 rounded" title="Preço anterior: R$ {{ number_format($wishlist->previous_price, 2, ',', '.') }}">
                                                <i class="fas fa-arrow-up mr-1"></i>
                                                {{ number_format((($wishlist->price - $wishlist->previous_price) / $wishlist->previous_price) * 100, 0) }}%
                                            </div>
                                        @endif
                                    @elseif($wishlist->previous_price && $wishlist->previous_price == $wishlist->price)
                                        <div class="flex items-center text-[10px] font-bold text-zinc-500 bg-zinc-100 dark:bg-zinc-800/50 px-1.5 py-0.5 rounded" title="Preço mantido: R$ {{ number_format($wishlist->previous_price, 2, ',', '.') }}">
                                            <i class="fas fa-minus mr-1"></i> 0%
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-xs text-zinc-500 italic">Preço não identificado</div>
                            @endif
                            
                            @if($wishlist->last_price_update)
                                <div class="text-[10px] text-zinc-400 mt-0.5" title="{{ $wishlist->last_price_update->format('d/m/Y H:i') }}">
                                    Atualizado {{ $wishlist->last_price_update->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ $wishlist->url }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 hover:bg-zinc-200 dark:hover:text-white dark:hover:bg-zinc-700 transition-colors flex-shrink-0" title="Abrir link original">
                            <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                    </div>
                </div>
                
                @if($wishlist->histories->count() > 1)
                    <div class="px-2 pb-2 mt-auto border-t border-zinc-100 dark:border-zinc-800/50">
                        <div id="chart-{{ $wishlist->id }}" class="w-full"></div>
                    </div>
                @endif
                
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-pink-50 dark:bg-pink-500/10 flex items-center justify-center mx-auto mb-4 text-pink-500 text-2xl">
            <i class="fas fa-heart"></i>
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-2">Sua lista está vazia</h3>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 max-w-sm mx-auto">Salve links de produtos da Shopee ou outras plataformas para acompanhar preços e não perder de vista.</p>
        <button onclick="openWishlistModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-sm">
            <i class="fas fa-plus"></i> Adicionar Primeiro Item
        </button>
    </div>
@endif

<!-- Add Modal -->
<div id="addWishlistModal" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm" onclick="closeWishlistModal()"></div>
    
    <div id="addWishlistContent" class="relative bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 m-4 transform scale-95 transition-transform duration-300">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/50">
            <h3 class="font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-link text-pink-500"></i> Adicionar à Lista
            </h3>
            <button onclick="closeWishlistModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="{{ route('wishlists.store') }}" method="POST" class="p-6">
            @csrf
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">Cole o link da Shopee (ou outro site). Nós tentaremos extrair fotos e preços automaticamente pra você.</p>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Link do Produto</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-globe text-zinc-400"></i>
                    </div>
                    <input type="url" name="url" required placeholder="https://shopee.com.br/produto..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <button type="button" onclick="closeWishlistModal()" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i>Buscando...'; this.classList.add('opacity-75', 'cursor-not-allowed')" class="px-4 py-2 bg-pink-500 text-white text-sm font-bold rounded-lg hover:bg-pink-600 transition-colors shadow-sm focus:outline-none">
                    Salvar na Lista
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function openWishlistModal() {
        const modal = document.getElementById('addWishlistModal');
        const content = document.getElementById('addWishlistContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
    }

    function closeWishlistModal() {
        const modal = document.getElementById('addWishlistModal');
        const content = document.getElementById('addWishlistContent');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Renderize ApexCharts para os Históricos de Preços
    document.addEventListener('DOMContentLoaded', function () {
        @if($wishlists->count() > 0)
            @foreach($wishlists as $wishlist)
                @if($wishlist->histories->count() > 1)
                    var options_{{ $wishlist->id }} = {
                        series: [{
                            name: 'Preço',
                            data: [{!! implode(',', $wishlist->histories->pluck('price')->toArray()) !!}]
                        }],
                        chart: {
                            type: 'area',
                            height: 60,
                            sparkline: { enabled: true },
                            animations: { enabled: false }
                        },
                        stroke: { curve: 'smooth', width: 2 },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0,
                                stops: [0, 100]
                            }
                        },
                        colors: ['#ec4899'], // Tailwind pink-500
                        tooltip: {
                            fixed: { enabled: false },
                            x: { show: false },
                            y: { show: false }, // we disable default y title
                            marker: { show: false },
                            custom: function({series, seriesIndex, dataPointIndex, w}) {
                                var val = series[seriesIndex][dataPointIndex];
                                var isDark = document.documentElement.classList.contains('dark');
                                
                                return `<div class="px-2 py-1 text-xs font-semibold rounded shadow-lg border ${
                                    isDark 
                                        ? 'bg-zinc-800 border-zinc-700 text-white' 
                                        : 'bg-white border-zinc-200 text-zinc-900'
                                }">R$ ${val.toFixed(2).replace('.', ',')}</div>`;
                            }
                        }
                    };
                    var chart_{{ $wishlist->id }} = new ApexCharts(document.querySelector("#chart-{{ $wishlist->id }}"), options_{{ $wishlist->id }});
                    chart_{{ $wishlist->id }}.render();
                @endif
            @endforeach
        @endif
    });
</script>
@endsection
